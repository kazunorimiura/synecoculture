<?php
/**
 * Polylangプラグインの機能強化
 *
 * @package wordpressfoundation
 */

/**
 * Polylangプラグインの機能強化クラス
 *
 * @since 0.1.0
 */
class WPF_Polylang_Functions {
	/**
	 * 言語間で同期する投稿メタキーの配列
	 *
	 * @var array
	 */
	public static $sync_post_meta_keys = array(
		'_wpf_languages_provided',
		'_wpf_show_toc',
		'_wpf_hide_search_engine',
		'_wpf_schema_type',
		'_wpf_related_members',
		'_wpf_pickup_flag',
		// SCFの `smart-cf-repeat-multiple-data` メタデータを同期する
		// SCFは 繰り返し可能カスタムフィールドの子フィールドに複数の値を持てるフィールド（配列型）が含まれる場合、
		// `'smart-cf-repeat-multiple-data' => array( 'key1' => array(1, 1, 2, 1), ... )`
		// のような構造の外部データを持つことによって、繰り返し可能カスタムフィールド内における順序と要素数を紐づける仕組み。
		// 本来、繰り返しフィールドごとに個別に同期するかどうかを設定したいが、ご覧の通り、キー名が共通であるため、
		// すべての繰り返しフィールドを強制的に同期することになってしまうが、このテーマでは現状それで問題がないため、利便性を優先している
		'smart-cf-repeat-multiple-data',
	);

	/**
	 * 新規作成時のみコピーする投稿メタキーの配列（同期はしない）
	 *
	 * @var array
	 */
	public static $copy_once_post_meta_keys = array(
		'_wpf_subtitle',
	);

	/**
	 * 言語間で同期するタームメタキーの配列
	 *
	 * @var array
	 */
	public static $sync_term_meta_keys = array(
		'_wpf_term_order',
	);

	/**
	 * コンストラクタ
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * アクションフック、フィルターフックを追加する。
	 *
	 * @since 0.1.0
	 */
	public function add_hooks() {
		add_action( 'init', array( $this, 'pll_register_strings' ), 10 );
		add_action( 'init', array( $this, 'register_meta' ), 10 );
		add_filter( 'nav_menu_link_attributes', array( $this, 'a11y_menu' ), 10, 3 );
		add_filter( 'pll_hide_archive_translation_url', array( $this, 'pll_hide_archive_translation_url' ) );
		add_filter( 'pll_copy_post_metas', array( $this, 'pll_copy_post_metas' ), 10, 4 );
		add_filter( 'pll_copy_term_metas', array( $this, 'pll_copy_term_metas' ), 10, 1 );
		add_filter( 'pll_post_metas_to_export', array( $this, 'export_post_metas' ), 10 );
		add_filter( 'pll_translate_post_meta', array( $this, 'translate_post_meta' ), 10, 3 );
	}

	/**
	 * 言語間で同期する投稿メタを翻訳する。
	 * 投稿IDを保存するメタデータなど、言語ごとに値が異なる投稿メタを翻訳する。
	 *
	 * @param string $value メタ値。
	 * @param string $key メタキー。
	 * @param string $lang 対象投稿の言語スラッグ。
	 * @return string
	 */
	public static function translate_post_meta( $value, $key, $lang ) {
		// 投稿から選択するタイプのメタデータで、かつサブ言語と同期するものは、サブ言語版の投稿が選択されるようにする
		if ( in_array(
			$key,
			array(
				'_wpf_related_members',
			),
			true
		) ) {
			if ( is_array( $value ) ) {
				$tr_post_ids = array();

				foreach ( $value as $post_id ) {
					$tr_post_id = pll_get_post( $post_id, $lang );
					array_push( $tr_post_ids, (string) $tr_post_id );
				}

				if ( ! empty( $tr_post_ids ) ) {
					$value = $tr_post_ids;
				}
			} elseif ( is_string( $value ) ) {
				$value = (string) pll_get_post( $value, $lang );
			}
		}

		return $value;
	}

	/**
	 * 文字列翻訳を登録する。
	 */
	public static function pll_register_strings() {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}

		$user_query = new WP_User_Query( array( 'number' => -1 ) );
		$users      = $user_query->get_results();
		foreach ( $users as $user ) {
			pll_register_string( 'wpf_' . $user->user_nicename . '_display_name', $user->display_name, __( 'ユーザー', 'wordpressfoundation' ) );
			pll_register_string( 'wpf_' . $user->user_nicename . '_position', $user->position, __( 'ユーザー', 'wordpressfoundation' ) );
		}

		$post_types = get_post_types( array( 'public' => true ) );
		foreach ( $post_types as $post_type ) {
			$post_type_object = get_post_type_object( $post_type );
			pll_register_string( 'wpf_' . $post_type . '_label_name', $post_type_object->labels->name, __( '投稿タイプ', 'wordpressfoundation' ) );
			pll_register_string( 'wpf_' . $post_type . '_description', $post_type_object->description, __( '投稿タイプ', 'wordpressfoundation' ) );
		}

		$languages = pll_the_languages( array( 'raw' => 1 ) );
		foreach ( $languages as $lang ) {
			pll_register_string( 'wpf_untranslated_content_notice', $lang['name'], __( 'テーマ', 'wordpressfoundation' ) );
		}

		pll_register_string( 'wpf_legal_notice', '協生農法は株式会社桜自然塾の商標または登録商標です。', __( 'テーマ', 'wordpressfoundation' ) );
		pll_register_string( 'wpf_legal_notice', 'Synecocultureはソニーグループ株式会社の商標です。', __( 'テーマ', 'wordpressfoundation' ) );
	}

	/**
	 * Polylangプラグインと連携する投稿メタを登録する。
	 *
	 * @return void
	 */
	public static function register_meta() {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}

		$languages      = pll_the_languages( array( 'raw' => 1 ) );
		$language_slugs = array();
		foreach ( $languages as $lang ) {
			array_push( $language_slugs, $lang['slug'] );
		}

		$post_types = get_post_types(
			array(
				'public' => true,
			)
		);

		unset( $post_types['attachment'] );

		foreach ( $post_types as $post_type ) {
			register_meta(
				'post',
				'_wpf_languages_provided',
				array(
					'type'           => 'array',
					'default'        => $language_slugs,
					'auth_callback'  => '__return_true',
					'show_in_rest'   => array(
						'schema' => array(
							'type'  => 'array',
							'items' => array(
								'type' => 'string',
							),
						),
					),
					'object_subtype' => $post_type,
					'single'         => true,
				)
			);
		}
	}

	/**
	 * 言語メニューのa11y対応
	 *
	 * @param array    $atts メニュー項目のa要素に適用されるHTML属性、空文字列は無視される。
	 * @param WP_Post  $item 現在のメニュー項目オブジェクト。
	 * @param stdClass $args wp_nav_menu() の引数のオブジェクト。
	 * @return array
	 */
	public function a11y_menu( $atts, $item, $args ) {
		if ( ! function_exists( 'pll_register_string' ) ) {
			return;
		}

		// 現在の言語にaria-current属性を追加
		if ( in_array( 'current-lang', $item->classes, true ) ) {
			$atts['aria-current'] = 'page';
		}

		// lang属性を除去
		// lang属性は要素自体の言語を定義するものであるため、「Ja」のような英語の文言を使っている場合、a11y違反となる。
		// なくても問題ない属性なので取り除いておく。
		if ( isset( $atts['lang'] ) && in_array( 'lang-item', $item->classes, true ) ) {
			unset( $atts['lang'] );
		}

		return $atts;
	}

	/**
	 * アーカイブページにおいて、投稿が1件もない場合、言語スイッチャーのリンク先が home_url() になるデフォルトの動作を特定のページで無効化する。
	 *
	 * @since 0.1.0
	 * @return bool false ならホームページへのリダイレクトを無効化する。
	 */
	public static function pll_hide_archive_translation_url() {
		// authorページは投稿がなくてもプロフィールページとして表示したい。
		if ( is_author() ) {
			return false;
		}
	}

	/**
	 * 特定の投稿メタを言語間で同期、または新規作成時のみコピー
	 *
	 * @since 0.1.0
	 * @param array $metas 投稿メタの配列
	 * @param bool  $sync  true: 同期、false: 新規作成時の複製
	 * @param int   $from  コピー元の投稿ID
	 * @param int   $to    コピー先の投稿ID
	 * @return array 同期/コピーする投稿メタスラッグの配列
	 */
	public static function pll_copy_post_metas( $metas, $sync, $from, $to ) {
		// 常に同期するメタキーを追加
		$metas = array_merge( $metas, self::$sync_post_meta_keys );

		// 新規作成時のみコピーするメタキーを追加
		if ( ! $sync ) {
			$metas = array_merge( $metas, self::$copy_once_post_meta_keys );
		}

		return $metas;
	}

	/**
	 * 特定のタームメタを言語間で同期
	 * なお、この設定を定義する前に保存した投稿には適用されない。
	 *
	 * @since 0.1.0
	 * @param array $metas 投稿メタの配列
	 * @return array 同期する投稿メタスラッグの配列
	 */
	public static function pll_copy_term_metas( $metas ) {
		return array_merge( $metas, self::$sync_term_meta_keys );
	}

	/**
	 * テーマの翻訳可能なメタをエクスポートに追加
	 *
	 * @param  array $metas エクスポートする投稿メタの配列 (メタキーでキー指定)
	 * @return array エクスポートする投稿メタの変更後の配列
	 */
	public static function export_post_metas( $metas ) {
		return array_merge(
			$metas,
			array_fill_keys( self::$sync_post_meta_keys, 1 ),
			array_fill_keys( self::$sync_term_meta_keys, 1 )
		);
	}

	/**
	 * 指定の投稿IDのコンテンツが翻訳されていない場合の通知メッセージを取得する。
	 * このメソッドは `_wpf_languages_provided` メタ値を参照する。
	 *
	 * @param string $post_id 投稿ID。
	 * @return string 翻訳されていない場合はその旨のメッセージ、そうでない場合は空文字列。
	 */
	public static function get_untranslated_content_notice( $post_id ) {
		$languages_provided = get_post_meta( $post_id, '_wpf_languages_provided', true );

		if ( empty( $languages_provided ) || false === $languages_provided ) {
			return '';
		}

		$language       = pll_the_languages( array( 'raw' => 1 ) );
		$language_slugs = array();
		foreach ( $language as $lang ) {
			array_push( $language_slugs, $lang['slug'] );
		}

		// 全言語で提供されている場合は何も返さない。
		if ( count( $language_slugs ) === count( $languages_provided ) ) {
			return '';
		}

		$provided_lang_names = array();
		foreach ( $languages_provided as $lang_slug ) {
			$lang = PLL()->model->get_language( $lang_slug );
			array_push( $provided_lang_names, pll__( $lang->name ) );
		}
		$provided_lang_names_str = implode( ',', $provided_lang_names );

		$current_language = pll_current_language( 'slug' );

		if ( ! in_array( $current_language, $languages_provided, true ) ) {
			if ( count( $languages_provided ) > 1 ) {
				/* translators: %s: 言語名 */
				return sprintf( __( 'このページは%sで提供されています。', 'wordpressfoundation' ), $provided_lang_names_str );
			} else {
				/* translators: %s: 言語名 */
				return sprintf( __( 'このページは%sでのみ提供されています。', 'wordpressfoundation' ), $provided_lang_names_str );
			}
		}

		return '';
	}
}
