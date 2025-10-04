<?php
/**
 * カスタム投稿タイプのパーマリンクを設定
 *
 * @package wordpressfoundation
 */

/**
 * カスタム投稿タイプのパーマリンクを設定する。
 *
 * @since 0.1.0
 */
class WPF_CPT_Permalink {

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
		add_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 4 );
		add_filter( 'author_link', array( $this, 'author_link' ), 10, 4 );
		add_filter( 'get_archives_link', array( $this, 'get_archives_link' ), 10, 2 );
	}

	/**
	 * アクションフック、フィルターフックを外す。
	 *
	 * @return void
	 * @since 0.1.0
	 */
	public function remove_hooks() {
		remove_filter( 'post_type_link', array( $this, 'post_type_link' ), 10, 4 );
		remove_filter( 'author_link', array( $this, 'author_link' ), 10, 4 );
		remove_filter( 'get_archives_link', array( $this, 'get_archives_link' ), 10, 2 );
	}

	/**
	 * カスタムパーマリンク構造に基づいてCPTの投稿パーマリンクを生成する。
	 *
	 * Inspired by WordPress:
	 * https://github.com/WordPress/wordpress-develop/blob/e8846318cf9a4c8d012ae32b4de9a51655237a24/src/wp-includes/link-template.php#LL170C10-L170C23
	 *
	 * @since 0.1.0
	 * @param string  $post_link 対象の投稿パーマリンク。
	 * @param WP_Post $post 対象の投稿オブジェクト。
	 * @param bool    $leavename 投稿名・固定ページ名の構造タグを保持するかどうか。デフォルトはfalse。
	 * @return string
	 */
	public function post_type_link( $post_link, $post, $leavename ) {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() ) {
			return $post_link;
		}

		$draft_or_pending = isset( $post->post_status ) && in_array(
			$post->post_status,
			array(
				'draft',
				'pending',
				'auto-draft',
			),
			true
		);
		if ( $draft_or_pending && ! $leavename ) {
			return $post_link;
		}

		$post_type        = $post->post_type;
		$post_type_object = get_post_type_object( $post_type );

		if ( false === $post_type_object->rewrite ) {
			return $post_link;
		}

		if ( ! in_array( $post_type, WPF_CPTP_Utils::get_post_types(), true ) ) {
			return $post_link;
		}

		// カスタム投稿タイプのパーマリンク構造を取得する。
		$permalink_structure = $wp_rewrite->get_extra_permastruct( WPF_CPT_Rewrite::get_cpt_permastruct_name( $post_type ) );

		// 投稿IDのリライトタグを実際のIDに置き換える。
		$permalink = str_replace( '%post_id%', $post->ID, $permalink_structure );

		// CPTリライトタグを実際のスラッグに置き換える。
		$permalink = str_replace( WPF_CPT_Rewrite::get_cpt_rewrite_tag( $post_type ), $post_type_object->rewrite['slug'], $permalink );

		// 親ページが存在する場合、親のパスを含む投稿名タグに置き換える。
		$parents_dirs = '';
		if ( $post_type_object->hierarchical ) {
			if ( ! $leavename ) {
				$post_id = $post->ID;
				$parent  = get_post( $post_id )->post_parent;
				while ( $parent ) {
					$parents_dirs = get_post( $parent )->post_name . '/' . $parents_dirs;
					$post_id      = $parent;
					$parent       = get_post( $post_id )->post_parent;
				}
			}
		}
		$permalink = str_replace( '%' . $post_type . '%', $parents_dirs . '%' . $post_type . '%', $permalink );

		if ( ! $leavename ) {
			$permalink = str_replace( '%' . $post_type . '%', $post->post_name, $permalink );
		}

		$rewritecode = array(
			'%year%',
			'%monthnum%',
			'%day%',
			'%hour%',
			'%minute%',
			'%second%',
			'%category%',
			'%author%',
		);

		/*
		 * パーマリンクは保存された post_date の値に基づいており、 PHP のデフォルトのタイムゾーンに関係
		 * なくローカルタイムとして解析されるため、これは API コールではない。デフォルトのタイムゾーンに関
		 * 係なく、現地時間として解析される。
		 */
		$date = explode( ' ', str_replace( array( '-', ':' ), ' ', $post->post_date ) );

		$category = '';
		if ( strpos( $permalink, '%category%' ) !== false ) {
			$cats = get_the_category( $post->ID );
			if ( $cats ) {
				$cats = wp_list_sort(
					$cats,
					array(
						'term_id' => 'ASC',
					)
				);

				/**
				 * カテゴリーのパーマリンクをフィルタリングする。
				 *
				 * @since 3.5.0
				 *
				 * @param WP_Term  $cat  パーマリンクで使用するカテゴリー。
				 * @param array    $cats 投稿に関連するすべてのカテゴリ (WP_Term オブジェクト) の配列。
				 * @param WP_Post  $post 対象の投稿オブジェクト。
				 */
				$category_object = apply_filters( 'wpf_post_link_category', $cats[0], $cats, $post );

				$category_object = get_term( $category_object, 'category' );
				$category        = $category_object->slug;
				if ( $category_object->parent ) {
					$category = get_category_parents( $category_object->parent, false, '/', true ) . $category;
				}
			}

			// パーマリンクにデフォルトのカテゴリーを設定し、
			// 明示的に割り当てる必要がないようにする。
			if ( empty( $category ) ) {
				$default_category = get_term( get_option( 'default_category' ), 'category' );
				if ( $default_category && ! is_wp_error( $default_category ) ) {
					$category = $default_category->slug;
				}
			}
		}

		$author = '';
		if ( false !== strpos( $permalink, '%author%' ) ) {
			$authordata = get_userdata( $post->post_author );
			$author     = $authordata->user_nicename;
		}

		$rewritereplace = array(
			$date[0],
			$date[1],
			$date[2],
			$date[3],
			$date[4],
			$date[5],
			$category,
			$author,
		);

		$taxonomies = WPF_CPTP_Utils::get_taxonomies( true );

		foreach ( $taxonomies as $taxonomy => $objects ) {
			if ( false !== strpos( $permalink, '%' . $taxonomy . '%' ) ) {
				$terms = get_the_terms( $post->ID, $taxonomy );

				if ( $terms && ! is_wp_error( $terms ) ) {
					$terms = wp_list_sort(
						$terms,
						array(
							'term_id' => 'ASC',
						)
					);

					/**
					 * タームのパーマリンクをフィルタリングする。
					 *
					 * @since 0.1.0
					 *
					 * @param WP_Term     $term_obj 選択されたターム。
					 * @param WP_Term[]   $terms    投稿に設定されたターム。
					 * @param WP_Taxonomy $taxonomy タームが所属するタクソノミーオブジェクト。
					 * @param WP_Post     $post     対象の投稿オブジェクト。
					 */
					$term_object = apply_filters( 'wpf_post_link_term', $terms[0], $terms, $taxonomy, $post );
					$term        = $term_object->slug;

					if ( $term_object->parent && ! empty( $objects->hierarchical ) && ! empty( $objects->rewrite['hierarchical'] ) ) {
						$term = self::get_term_parents( $term_object->parent, $taxonomy, false, '/', true ) . $term;
					}
				}

				// パーマリンクにデフォルトのカテゴリーを設定し、明示的に割り当てる必要がないようにする。
				// ただし、default_term['slug'] オプションがある場合はそれを優先する。
				if ( empty( $term ) ) {
					$default_category = get_term( get_option( 'default_category' ), 'category' );
					if ( $default_category && ! is_wp_error( $default_category ) ) {
						$term = $default_category->slug;
					}

					if ( ! empty( $objects->default_term ) && ! empty( $objects->default_term['slug'] ) ) {
						$term = $objects->default_term['slug'];
					}
				}

				$rewritecode[]    = '%' . $taxonomy . '%';
				$rewritereplace[] = $term;
			}
		}

		$permalink = home_url( str_replace( $rewritecode, $rewritereplace, $permalink ) );

		return user_trailingslashit( $permalink, 'single' );
	}

	/**
	 * タームの親をセパレータで返す。
	 *
	 * @since 0.1.0
	 * @param int    $term_id タームID。
	 * @param int    $taxonomy 所属するタクソノミー。
	 * @param bool   $link        オプション。リンクでフォーマットするかどうか。デフォルトはfalse。
	 * @param string $separator   オプション。タームの区切り方。デフォルトは '/' 。
	 * @param bool   $nicename    オプション。nicenameを使うかどうか。デフォルトはfalse。
	 * @return string|WP_Error 成功するとタームの親のリスト、失敗するとWP_Errorが表示される。
	 */
	public static function get_term_parents( $term_id, $taxonomy, $link = false, $separator = '/', $nicename = false ) {
		$format = $nicename ? 'slug' : 'name';

		$args = array(
			'separator' => $separator,
			'link'      => $link,
			'format'    => $format,
		);

		return get_term_parents_list( $term_id, $taxonomy, $args );
	}

	/**
	 * 著者リンクを修正する。
	 *
	 * 例: /dpt/author/user => /cpt/author/user
	 *
	 * @param string $link            著者リンクURL。
	 * @param int    $author_id       著者ID。
	 * @param string $author_nicename 著者スラッグ。
	 * @return string
	 */
	public function author_link( $link, $author_id, $author_nicename ) {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() ) {
			return $link;
		}

		$post_type = get_post_type();

		if ( ! in_array( $post_type, WPF_CPTP_Utils::get_post_types(), true ) ) {
			return $link;
		}

		$post_type_object = get_post_type_object( $post_type );

		if ( ! empty( $post_type_object->has_archive ) && is_string( $post_type_object->has_archive ) ) {
			$author_link = str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $post_type ), '%author%' ), array( $post_type_object->has_archive, $author_nicename ), WPF_CPT_Rewrite::get_author_permastruct( $post_type ) );
		} else {
			$author_link = str_replace( array( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $post_type ), '%author%' ), array( $post_type_object->rewrite['slug'], $author_nicename ), WPF_CPT_Rewrite::get_author_permastruct( $post_type ) );
		}

		if ( $post_type_object->rewrite['with_front'] ) {
			$author_link = substr( $wp_rewrite->front, 1 ) . $author_link;
		} else {
			$author_link = $wp_rewrite->root . $author_link;
		}

		return home_url( user_trailingslashit( $author_link ) );
	}

	/**
	 * 日付アーカイブリンクを修正する。
	 *
	 * 例: /dpt/date/2012/12/12/?post_type=cpt => /cpt/date/2012/12/12/
	 *
	 * wp_get_archives が出力するパーマリンク構造は、デフォルト投稿タイプの場合、パーマリンク構造の3階層以内に
	 * %post_id% が存在する場合は、/date/{YEAR}/{MONTH}/{DAY}/、そうでない場合は /{YEAR}/{MONTH}/{DAY}/
	 * となり、カスタム投稿タイプの場合、デフォルトの日付パーマリンク構造にクエリパラメータとしてカスタム投稿タイプ
	 * が付与される。たとえば、デフォルトのパーマリンク構造が /news/%post_id%/ だった場合、カスタム投稿タイプの
	 * 日付パーマリンクは /news/date/2023/?post_type=blog のような形式になるが、これを /blog/date/2023/
	 * のように変更して、デフォルトの構造と統一する。ただし、これを変更する都合の良いフックはないので、htmlを文字列
	 * 置換して実現している。
	 *
	 * @link https://developer.wordpress.org/reference/hooks/get_archives_link/
	 *
	 * @since 0.1.0
	 * @param string $link_html アーカイブリンクのHTML文字列。
	 * @param string $url アーカイブへのURL。
	 * @return string
	 */
	public static function get_archives_link( $link_html, $url ) {
		global $wp_rewrite;

		if ( ! $wp_rewrite->using_permalinks() ) {
			return $link_html;
		}

		// wp_get_archives が呼び出そうとしている投稿タイプを取得する
		// 意図: wp_get_archives で渡される可能性のある post_type オプションは get_archives_link フックから
		// は取得できないため、$url 引数のクエリ文字列を参照して post_type オプションが設定されているかどうかを判断している
		// post_type クエリがなければ、リクエストされたURLの post_type クエリを使用する。
		$parsed_url = wp_parse_url( $url );
		if ( ! isset( $parsed_url['query'] ) ) {
			return $link_html;
		}
		parse_str( $parsed_url['query'], $params );
		$post_type = isset( $params['post_type'] ) ? $params['post_type'] : WPF_Utils::get_post_type( 'post_type' );

		if ( ! empty( $post_type ) && 'post' !== $post_type ) {
			if ( ! $wp_rewrite->using_permalinks() ) {
				return $link_html;
			}

			$date_permalink_structure = $wp_rewrite->get_date_permastruct();
			$date_front               = substr( $date_permalink_structure, 0, strpos( $date_permalink_structure, '%' ) );

			$post_type_object = get_post_type_object( $post_type );

			if ( ! empty( $post_type_object->has_archive ) && is_string( $post_type_object->has_archive ) ) {
				$cpt_date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $post_type ), $post_type_object->has_archive, WPF_CPT_Rewrite::get_date_front( $post_type ) );
			} else {
				$cpt_date_front = str_replace( WPF_CPT_Rewrite::get_cpt_archive_rewrite_tag( $post_type ), $post_type_object->rewrite['slug'], WPF_CPT_Rewrite::get_date_front( $post_type ) );
			}

			if ( $post_type_object->rewrite['with_front'] ) {
				$cpt_date_front = substr( $wp_rewrite->front, 1 ) . $cpt_date_front;
			} else {
				$cpt_date_front = $wp_rewrite->root . $cpt_date_front;
			}

			// post_type パラメータを href 値から除去する
			// $url 引数をデコードしている理由: $url は esc_url 関数を経由してHTMLエンコードされており、
			// クエリの結合文字（&）が「&#038;」に置換されている。そのため、HTMLエンコードされた文字列を
			// そのまま remove_query_arg に通してしまうと、ハッシュ値と誤認識され、リンクが壊れる
			$link_html = str_replace( $url, remove_query_arg( 'post_type', html_entity_decode( $url ) ), $link_html );

			// フロント文字列の置換
			if ( ! empty( $cpt_date_front ) ) {
				// デフォルトの日付パーマリンク構造にフロント文字列がある場合、$cpt_date_front に置き換える。
				if ( '/' !== $date_front ) {
					$link_html = str_replace( trim( $date_front, '/' ), trim( $cpt_date_front, '/' ), $link_html );

					// デフォルトの日付パーマリンク構造にフロント文字列がない場合、FQDNとパスの間に $cpt_date_front を差し込む。
				} else {
					$link_html = str_replace( home_url(), home_url( '/' . trim( $cpt_date_front, '/' ) ), $link_html );
				}
			}
		}

		return $link_html;
	}
}
