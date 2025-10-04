<?php
/**
 * カスタム投稿タイプのリライトルールを設定
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/**
 * カスタム投稿タイプのリライトルールを設定する。
 *
 * @since 0.1.0
 */
class WPF_CPT_Rewrite {

	const DEFAULT_PERMALINK_STRUCTURE = '/%post_id%/';

	/**
	 * コンストラクタ。フィルターをフックする。
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * アクションフック、フィルターフックを追加する。
	 *
	 * @return void
	 * @since 0.1.0
	 */
	public function add_hooks() {
		add_action( 'registered_post_type', array( $this, 'register_post_type_rules' ), 10, 2 );
		add_filter( 'pll_rewrite_rules', array( $this, 'pll_rewrite_rules' ), 10, 1 ); // for Polylang plugin.
	}

	/**
	 * アクションフック、フィルターフックを外す。
	 *
	 * @return void
	 * @since 0.1.0
	 */
	public function remove_hooks() {
		remove_action( 'registered_post_type', array( $this, 'register_post_type_rules' ), 10, 2 );
		remove_filter( 'pll_rewrite_rules', array( $this, 'pll_rewrite_rules' ), 10, 1 );
	}

	/**
	 * カスタム投稿タイプのリライトルールを追加する。
	 *
	 * @since 0.1.0
	 *
	 * @param string       $post_type 投稿タイプ。
	 * @param WP_Post_Type $args      投稿タイプの登録に使用された引数。
	 */
	public function register_post_type_rules( $post_type, $args ) {
		/**
		 * WP_Rewrite.
		 *
		 * @var WP_Rewrite $wp_rewrite
		 */
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() ) {
			return;
		}

		if ( ! in_array( $post_type, WPF_CPTP_Utils::get_post_types(), true ) ) {
			return;
		}

		// 古いパラメータである $with_front と $ep_mask の後方互換性対応:
		// https://github.com/WordPress/wordpress-develop/blob/9fae40ae696d771e044b2913bd54a28244c1901b/src/wp-includes/class-wp-rewrite.php#LL1793C11-L1793C17
		if ( ! is_array( $args->rewrite ) ) {
			$args->rewrite = array( 'with_front' => $args->rewrite );
		}

		$post_type_object = get_post_type_object( $post_type );

		$cpt_rewrite_tag = self::get_cpt_rewrite_tag( $post_type );

		/*
			* CPTスラッグのリライトタグを追加する。
			*
			* 意図: カスタム投稿タイプのクエリ生成を、このリライトタグに任せ、組み込みのリライトタグを使える
			* ようにすることで、WPコアの関数（generate_rewrite_rules）にルール生成を任せることができる。
			* generate_rewrite_rules 関数は、%post_id% などの存在を判定しながらルールを生成している。
			*
			* &pt_name= の存在意義: add_rewrite_tag の第三引数（クエリ部分）の末尾は「=」で終わる必要が
			* あるため、&pt_name= はダミー:
			* https://developer.wordpress.org/reference/functions/add_rewrite_tag/#parameters
			*
			* HACK: pt_name クエリの命名理由は、Polylangが rewrite_rules_array フックから
			* post_type={post_type} を含み、かつ name= を含まないクエリを WP_Post_type::add_rewrite_rules
			* の前処理で追加されるCPTアーカイブのルールと見なし、サブ言語ルールを生成するため、name を含む文字列にし
			* て、サブ言語ルールの正規表現が二重（例: (en)/(en)）に付与されないようにしている。
			*/
		add_rewrite_tag( $cpt_rewrite_tag, '(' . $post_type_object->rewrite['slug'] . ')', 'post_type=' . $post_type . '&pt_name=' );

		// CPTアーカイブスラッグのリライトタグを追加する。
		$cpt_archive_rewrite_tag = self::get_cpt_archive_rewrite_tag( $post_type );
		if ( ! empty( $post_type_object->has_archive ) && is_string( $post_type_object->has_archive ) ) {
			add_rewrite_tag( $cpt_archive_rewrite_tag, '(' . $post_type_object->has_archive . ')', 'post_type=' . $post_type . '&pt_name=' );
		} else {
			add_rewrite_tag( $cpt_archive_rewrite_tag, '(' . $post_type_object->rewrite['slug'] . ')', 'post_type=' . $post_type . '&pt_name=' );
		}

		// 正規表現マッチのクエリを $matches[n] にする。
		// https://github.com/WordPress/wordpress-develop/blob/f0a27d908b41368b568189c48b65d873610f5e99/src/wp-includes/class-wp-rewrite.php#L1488
		$wp_rewrite->matches = 'matches';

		if ( WPF_CPTP_Utils::get_post_type_author_archive_support( $post_type ) ) {
			$wp_rewrite->add_permastruct(
				self::get_author_permastruct_name( $post_type ),
				self::get_author_permastruct( $post_type ),
				array(
					'with_front' => $args->rewrite['with_front'],
					'ep_mask'    => EP_AUTHORS,
					'paged'      => $args->rewrite['pages'],
					'feed'       => $args->rewrite['feeds'],
					'walk_dirs'  => false,
				)
			);
		}

		if ( WPF_CPTP_Utils::get_post_type_date_archive_support( $post_type ) ) {
			$wp_rewrite->add_permastruct(
				self::get_year_permastruct_name( $post_type ),
				self::get_year_permastruct( $post_type ),
				array(
					'with_front' => $args->rewrite['with_front'],
					'ep_mask'    => EP_DATE,
					'paged'      => $args->rewrite['pages'],
					'feed'       => $args->rewrite['feeds'],
					'walk_dirs'  => false,
				)
			);

			$wp_rewrite->add_permastruct(
				self::get_month_permastruct_name( $post_type ),
				self::get_month_permastruct( $post_type ),
				array(
					'with_front' => $args->rewrite['with_front'],
					'ep_mask'    => EP_DATE,
					'paged'      => $args->rewrite['pages'],
					'feed'       => $args->rewrite['feeds'],
					'walk_dirs'  => false,
				)
			);

			$wp_rewrite->add_permastruct(
				self::get_date_permastruct_name( $post_type ),
				self::get_date_permastruct( $post_type ),
				array(
					'with_front' => $args->rewrite['with_front'],
					'ep_mask'    => EP_DATE,
					'paged'      => $args->rewrite['pages'],
					'feed'       => $args->rewrite['feeds'],
					'walk_dirs'  => false,
				)
			);
		}

		// 既存のCPTパーマリンク構造を削除する。
		$wp_rewrite->remove_permastruct( $post_type );

		$wp_rewrite->add_permastruct(
			self::get_cpt_permastruct_name( $post_type ),
			self::get_cpt_permastruct( $post_type ),
			array(
				'with_front' => $args->rewrite['with_front'],
				'ep_mask'    => $args->rewrite['ep_mask'],
				'paged'      => $args->rewrite['pages'],
				'feed'       => $args->rewrite['feeds'],
				'walk_dirs'  => false,
			)
		);
	}

	/**
	 * CPT著者パーマリンク構造名を返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return string
	 */
	public static function get_author_permastruct_name( $post_type ) {
		return 'wpf_' . $post_type . '_author';
	}

	/**
	 * CPT著者パーマリンク構造を返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return bool|string CPT著者パーマリンク構造。デフォルトパーマリンク構造が空の場合は false。
	 */
	public static function get_author_permastruct( $post_type ) {
		global $wp_rewrite;
		if ( empty( $wp_rewrite->permalink_structure ) ) {
			return false;
		}

		return self::get_cpt_archive_rewrite_tag( $post_type ) . '/' . self::get_author_base( $post_type ) . '/%author%';
	}

	/**
	 * CPT著者ベースを返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return string
	 */
	public static function get_author_base( $post_type ) {
		$post_type_object = get_post_type_object( $post_type );

		$is_author_archive_a_string = ! empty( $post_type_object->wpf_cptp ) && isset( $post_type_object->wpf_cptp['author_archive'] ) && is_string( $post_type_object->wpf_cptp['author_archive'] );
		if ( $is_author_archive_a_string ) {
			$author_base = $post_type_object->wpf_cptp['author_archive'];
		} else {
			global $wp_rewrite;
			$author_base = $wp_rewrite->author_base;
		}

		return $author_base;
	}

	/**
	 * CPT日付パーマリンク構造名を返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return string
	 */
	public static function get_date_permastruct_name( $post_type ) {
		return 'wpf_' . $post_type . '_date';
	}

	/**
	 * CPT日付パーマリンク構造を返す。
	 *
	 * wp-includes/class-wp-rewrite.php の get_date_permastruct メソッドを参考にしている:
	 * https://github.com/WordPress/wordpress-develop/blob/4ad3cad2e0d9369d96f04837babe6270a1423487/src/wp-includes/class-wp-rewrite.php#L495
	 *
	 * @since 0.1.0
	 * @param string $post_type 投稿タイプ。
	 * @return bool|string CPT日付パーマリンク構造。デフォルトパーマリンク構造が空の場合は false。
	 */
	public static function get_date_permastruct( $post_type ) {
		global $wp_rewrite;

		if ( empty( $wp_rewrite->permalink_structure ) ) {
			return false;
		}

		$permalink_structure = WPF_CPTP_Utils::get_permalink_structure( $post_type );

		if ( empty( $permalink_structure ) ) {
			$permalink_structure = self::DEFAULT_PERMALINK_STRUCTURE;
		}

		$permalink_structure = self::get_cpt_archive_rewrite_tag( $post_type ) . $permalink_structure;

		$endians = array( '%year%/%monthnum%/%day%', '%day%/%monthnum%/%year%', '%monthnum%/%day%/%year%' );

		$date_endian = '';

		foreach ( $endians as $endian ) {
			if ( false !== strpos( $permalink_structure, $endian ) ) {
				$date_endian = $endian;
				break;
			}
		}

		if ( empty( $date_endian ) ) {
			$date_endian = '%year%/%monthnum%/%day%';
		}

		return self::get_date_front( $post_type ) . $date_endian;
	}

	/**
	 * CPT日付パーマリンクのフロント文字列を返す。
	 *
	 * wp-includes/class-wp-rewrite.php の get_date_permastruct メソッドを参考にしている:
	 * https://github.com/WordPress/wordpress-develop/blob/4ad3cad2e0d9369d96f04837babe6270a1423487/src/wp-includes/class-wp-rewrite.php#L495
	 *
	 * @since 0.1.0
	 * @param string $post_type 投稿タイプ。
	 * @return string
	 */
	public static function get_date_front( $post_type ) {
		global $wp_rewrite;

		$permalink_structure = WPF_CPTP_Utils::get_permalink_structure( $post_type );

		if ( empty( $permalink_structure ) ) {
			$permalink_structure = self::DEFAULT_PERMALINK_STRUCTURE;
		}

		$permalink_structure = self::get_cpt_archive_rewrite_tag( $post_type ) . $permalink_structure;

		/*
		 * パーマリンク構造において、日付タグと %{$post_type}_id% が重ならないようにする。
		 * もしそうなら、日付タグを $front/date/ に移動する。
		 */
		$front = substr( $permalink_structure, 0, strpos( $permalink_structure, '%' ) ) . self::get_cpt_archive_rewrite_tag( $post_type ) . '/';
		preg_match_all( '/%.+?%/', $permalink_structure, $tokens );
		$tok_index = 1;
		foreach ( (array) $tokens[0] as $token ) {
			if ( '%post_id%' === $token && ( $tok_index <= 3 ) ) {
				$front = $front . 'date/';
				break;
			}
			$tok_index++;
		}

		return $front;
	}

	/**
	 * CPT年パーマリンク構造名を返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return string
	 */
	public static function get_year_permastruct_name( $post_type ) {
		return 'wpf_' . $post_type . '_year';
	}

	/**
	 * CPT年パーマリンク構造を返す。
	 *
	 * 日付のパーマリンク構造を取得し、月と日のパーマリンク構造を除去する。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return string|false 成功の場合は年パーマリンク構造、失敗した場合は false
	 */
	public static function get_year_permastruct( $post_type ) {
		$structure = self::get_date_permastruct( $post_type );

		if ( empty( $structure ) ) {
			return false;
		}

		$structure = str_replace( '%monthnum%', '', $structure );
		$structure = str_replace( '%day%', '', $structure );
		$structure = preg_replace( '#/+#', '/', $structure );

		return $structure;
	}

	/**
	 * CPT年月パーマリンク構造名を返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return string
	 */
	public static function get_month_permastruct_name( $post_type ) {
		return 'wpf_' . $post_type . '_month';
	}

	/**
	 * CPT年月パーマリンク構造を返す。
	 *
	 * 日付のパーマリンク構造を取得し、日のパーマリンク構造を除去する。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return string|false 成功の場合は年月パーマリンク構造、失敗した場合は false
	 */
	public static function get_month_permastruct( $post_type ) {
		$structure = self::get_date_permastruct( $post_type );

		if ( empty( $structure ) ) {
			return false;
		}

		$structure = str_replace( '%day%', '', $structure );
		$structure = preg_replace( '#/+#', '/', $structure );

		return $structure;
	}

	/**
	 * カスタム投稿タイプのパーマリンク構造名を返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return string
	 */
	public static function get_cpt_permastruct_name( $post_type ) {
		return 'wpf_' . $post_type;
	}

	/**
	 * CPTパーマリンク構造を返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 対象の投稿タイプ。
	 * @return bool|string CPTパーマリンク構造。デフォルトパーマリンク構造が空の場合は false。
	 */
	public static function get_cpt_permastruct( $post_type ) {
		global $wp_rewrite;
		if ( empty( $wp_rewrite->permalink_structure ) ) {
			return false;
		}

		$permalink_structure = WPF_CPTP_Utils::get_permalink_structure( $post_type );

		if ( empty( $permalink_structure ) ) {
			$permalink_structure = self::DEFAULT_PERMALINK_STRUCTURE;
		}

		// %postname% を %{post_type}% に置き換える。
		// %{post_type}% タグは、コアによって投稿名に置き換えられる。
		// ただし、rewrite が false でない場合のみ。
		$permalink_structure = str_replace( '%postname%', '%' . $post_type . '%', $permalink_structure );

		return self::get_cpt_rewrite_tag( $post_type ) . $permalink_structure;
	}

	/**
	 * CPTパーマリンク構造におけるCPTスラッグのリライトタグを返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 投稿タイプ。
	 * @return string
	 */
	public static function get_cpt_rewrite_tag( $post_type ) {
		return '%' . $post_type . '_slug%';
	}

	/**
	 * CPTパーマリンク構造におけるCPTアーカイブスラッグのリライトタグを返す。
	 *
	 * @since 0.1.0
	 * @param string $post_type 投稿タイプ。
	 * @return string
	 */
	public static function get_cpt_archive_rewrite_tag( $post_type ) {
		return '%' . $post_type . '_archive_slug%';
	}

	/**
	 * Polylangプラグインのサブ言語ルールをCPTパーマリンク構造にも適用する。
	 *
	 * Polylangは WP_Rewrite_rule::rewrite_rules の各パーマリンク構造
	 * タイプのフィルタ（例: post_rewrite_rules）をフックしてサブ言語ルー
	 * ルを生成しているため、regestered_post_type で追加されるCPTパーマリ
	 * ンク構造はスルーされてしまう。
	 *
	 * @since 0.1.0
	 * @param array $rules リライトルールフィルタの配列。
	 * @return array
	 */
	public function pll_rewrite_rules( $rules ) {
		$post_types = WPF_CPTP_Utils::get_post_types();

		foreach ( $post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );

			if ( WPF_CPTP_Utils::get_post_type_date_archive_support( $post_type ) ) {
				array_push( $rules, self::get_author_permastruct_name( $post_type ) );
				array_push( $rules, self::get_year_permastruct_name( $post_type ) );
				array_push( $rules, self::get_month_permastruct_name( $post_type ) );
				array_push( $rules, self::get_date_permastruct_name( $post_type ) );
			}

			array_push( $rules, self::get_cpt_permastruct_name( $post_type ) );
		}

		return $rules;
	}
}
