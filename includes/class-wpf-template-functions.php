<?php
/**
 * WPをフックして、テーマの機能を強化
 *
 * @package wordpressfoundation
 */

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

/**
 * WPをフックして、テーマの機能を強化する。
 *
 * @since 0.1.0
 */
class WPF_Template_Functions {

	/**
	 * コンストラクタ
	 *
	 * @since 0.1.0
	 */
	public function __construct() {
		$this->add_hooks();
	}

	/**
	 * 各種フックを追加する。
	 *
	 * @since 0.1.0
	 */
	public function add_hooks() {
		// テーマサポートを設定する。
		add_action( 'after_setup_theme', array( $this, 'setup_theme' ) );

		// ナビゲーションメニューを登録する。
		add_action( 'after_setup_theme', array( $this, 'register_nav_menus' ) );

		// カスタムタクソノミーを登録する。
		add_action( 'init', array( $this, 'register_taxonomies' ) );

		// カスタム投稿タイプを登録する。
		add_action( 'init', array( $this, 'register_post_types' ) );

		// カスタム投稿メタを追加する。
		add_action( 'init', array( $this, 'register_meta' ) );

		// ウィジェットを登録する。
		add_action( 'widgets_init', array( $this, 'register_sidebar' ) );

		// フロントエンドにスタイルシートを追加する。
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );

		// フロントエンドにスクリプトを追加する。
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// ブロックエディタにスクリプトを追加する。
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_block_editor_scripts' ) );

		/**
		 * a11y
		 */

		// 特定のアンカー（ #menu ）が付与されたナビゲーションメニューのキーボードフォーカスを無効にする。
		add_filter( 'nav_menu_link_attributes', array( $this, 'disable_keyboard_focus_with_specific_anchor' ), 10, 3 );

		/**
		 * セキュリティ
		 */

		// WPが出力するバージョン情報を削除する。
		add_action( 'get_header', array( $this, 'remove_wp_generator' ) );

		// WP REST APIへのアクセスをホワイトリスト形式で許可する。
		add_filter( 'rest_pre_dispatch', array( $this, 'protect_rest_api' ), 10, 3 );

		// コメントのクラス属性値からユーザー名を特定できないようにする。
		add_filter( 'comment_class', array( $this, 'hide_username_from_comment_class' ) );

		// XML-RPCを無効にする。
		add_filter( 'xmlrpc_enabled', '__return_false' );

		// canonical URL からユーザー名が露出するのを防ぐ。
		add_filter( 'redirect_canonical', array( $this, 'redirect_canonical' ), 10, 2 );

		/**
		 * SEO
		 */

		// 特定のページを404にする。
		add_action( 'template_redirect', array( $this, 'redirect_301' ) );

		// ドキュメントタイトルをフィルタリング。
		add_filter( 'document_title_parts', array( $this, 'filter_document_title_parts' ) );

		// canonical URLをフィルタリング。
		add_filter( 'get_canonical_url', array( $this, 'filter_get_canonical_url' ), 10, 2 );

		// 主要なSEOプラグインが有効化されていない場合、テーマのSEO設定を有効にする
		if ( ! is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) && ! is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
			// wp_get_canonical_url は、アーカイブページ（アイテムのコレクション）に
			// canonicalタグを出力しない（wordpressfoundationにとって）中途半端な仕様なので、WP側
			// の出力を停止して、テーマ側で出力（the_seo_tag）する。
			remove_action( 'embed_head', 'rel_canonical' );
			remove_action( 'wp_head', 'rel_canonical' );

			// 検索エンジンにインデックスしたくないページに noindex を設定する。
			add_filter( 'wp_robots', array( $this, 'no_robots' ) );

			// _wpf_hide_search_engineがtrueの投稿をサイトマップから除外する
			add_filter( 'wp_sitemaps_posts_query_args', array( $this, 'exclude_posts_from_sitemap' ), 10, 2 );

			// SEO関連のHTMLタグを出力する。
			add_action( 'wp_head', array( $this, 'the_seo_tag' ) );
		}

		// スキーマタグを追加
		add_action( 'wp_head', array( $this, 'add_schema' ) );

		/*
		 * コメントを完全に無効化する。
		 */

		if ( get_option( 'wpf_disable_comments' ) ) {
			// 管理バーからコメントリンクを削除する。
			add_action( 'init', array( $this, 'remove_comments_menu_from_adminbar' ) );

			// コメント管理画面へのアクセスを拒否する。
			add_action( 'admin_init', array( $this, 'deny_access_to_comments_admin' ) );

			// ダッシュボードからコメントメタボックスを削除する。
			add_action( 'admin_init', array( $this, 'remove_comments_meta_box' ) );

			// 全ての投稿タイプのコメントサポートを無効にする。
			add_action( 'admin_init', array( $this, 'disable_all_post_types_comments_support' ) );

			// コメント関連の管理メニューを削除する。
			add_action( 'admin_menu', array( $this, 'remove_comments_menu_from_admin' ) );

			// リクエストヘッダの X-Pingback を無効化する。
			add_filter( 'wp_headers', array( $this, 'disable_x_pingback' ), 10, 2 );

			// 既存のコメントを非表示にする。
			add_filter( 'comments_array', array( $this, 'return_empty_array' ) );

			// フロントエンドのコメントを閉じる。
			add_filter( 'comments_open', array( $this, 'return_false' ) );
			add_filter( 'pings_open', array( $this, 'return_false' ) );
		}

		/**
		 * 管理画面のカスタマイズ。
		 */

		// 「投稿」のラベル名を変更する。
		add_action( 'init', array( $this, 'change_default_post_type_labels' ) );

		// 管理画面の「設定」にカスタム項目を追加する。
		add_action( 'admin_init', array( $this, 'add_settings' ) );

		// 管理画面のコアアップデートを促す通知を非表示にする。
		add_action( 'admin_menu', array( $this, 'hide_core_update_notice' ) );

		// 管理画面のフッターメッセージ（WordPressのご利用ありがとうございます）を非表示にする。
		add_filter( 'admin_footer_text', '__return_empty_string' );

		// リビジョンの保存数を制限する。
		add_filter( 'wp_revisions_to_keep', array( $this, 'revisions_to_keep' ), 10, 2 );

		// WPコアのメジャーバージョンの自動更新を無効化する。
		add_filter( 'allow_major_auto_core_updates', '__return_false' );

		// WPコアのマイナーバージョンの自動更新を無効化する。
		add_filter( 'allow_minor_auto_core_updates', '__return_false' );

		// プラグインの自動更新を無効化する。
		add_filter( 'auto_update_plugin', '__return_false' );

		// テーマの自動更新を無効化する。
		add_filter( 'auto_update_theme', '__return_false' );

		// 翻訳の自動更新を無効化する。
		add_filter( 'auto_update_translation', '__return_false' );

		// 管理画面の投稿一覧にアイキャッチ画像を表示する。
		add_action( 'manage_posts_custom_column', array( $this, 'add_thumbnail_column' ), 10, 2 );
		add_filter( 'manage_posts_columns', array( $this, 'add_thumbnail_column_style' ) );
		add_action( 'manage_pages_custom_column', array( $this, 'add_thumbnail_column' ), 10, 2 );
		add_filter( 'manage_pages_columns', array( $this, 'add_thumbnail_column_style' ) );

		/**
		 * 機能強化・その他
		 */

		// メインクエリのカスタマイズ
		add_action( 'pre_get_posts', array( $this, 'custom_main_query' ) );

		// モバイルにおける1ページあたりの表示件数を変更する。
		add_action( 'pre_get_posts', array( $this, 'set_posts_per_page_on_mobile' ) );

		// ソーシャルリンクナビゲーションのメニューテキストをSVGアイコンに置換する。
		add_filter( 'walker_nav_menu_start_el', array( $this, 'replace_to_social_icon' ), 10, 4 );

		// ナビゲーションメニューのアンカー要素にクラス属性を設定できるようにする。
		add_filter( 'nav_menu_link_attributes', array( $this, 'set_nav_menu_link_classes' ), 10, 3 );

		// CPT投稿用ページでも、メニュー項目にカレントクラスを追加する
		add_filter( 'nav_menu_css_class', array( $this, 'nav_menu_item_current_class' ), 10, 4 );

		// CPT投稿用ページでも、aria-current="page" を適用する
		add_filter( 'nav_menu_link_attributes', array( $this, 'nav_menu_item_aria_current' ), 10, 3 );

		// ページメニューのアンカー要素にクラス属性を設定できるようにする。
		add_filter( 'page_menu_link_attributes', array( $this, 'set_page_menu_link_classes' ), 10, 5 );

		// 抜粋の接尾辞を変更する。
		add_filter( 'excerpt_more', array( $this, 'excerpt_more' ) );

		// ページ区切りを番号付きページネーションにする。
		add_filter( 'wp_link_pages_args', array( $this, 'single_paginate_link' ) );

		// WPが出力するアーカイブタイトルの接頭辞を削除する。
		add_filter( 'get_the_archive_title_prefix', array( $this, 'remove_archive_title_prefix' ) );

		// カテゴリ名・タグ名を検索対象に含める。
		add_filter( 'posts_search', array( $this, 'search_include_terms' ), 10, 2 );

		// wp_get_archives の li, a タグに独自のクラスを追加する。
		add_filter( 'get_archives_link', array( $this, 'add_attributes_to_archives_link' ), 10, 3 );

		// body 要素に class 属性を追加する。
		add_filter( 'body_class', array( $this, 'body_class' ), 10, 1 );

		// タームオーダーに基づいて`member`投稿のメニューオーダーを設定
		add_action( 'save_post', array( $this, 'save_post_update_member_menu_order_by_term_order' ), 10, 3 );

		// `member_cat` タクソノミータームを更新した時に `_wpf_term_order` 値に基づいてメンバーの `menu_order' を更新
		add_action( 'saved_term', array( $this, 'save_term_update_member_menu_order_by_term_order' ), 11, 5 );
	}

	/**
	 * テーマサポートを設定する。
	 *
	 * WordPressの様々な機能のサポートを追加する。
	 *
	 * @since 0.1.0
	 */
	public static function setup_theme() {
		// テーマの翻訳を可能にする。
		load_theme_textdomain( 'wordpressfoundation', get_template_directory() . '/languages' );

		// 投稿やコメントのRSSフィードリンクをheadに追加する。
		add_theme_support( 'automatic-feed-links' );

		// タイトルタグを出力する。
		add_theme_support( 'title-tag' );

		// サムネイルを有効にする。
		add_theme_support( 'post-thumbnails' );

		// 検索フォーム、コメントフォーム、およびコメントのデフォルトのコアマークアップを有効なHTML5を出力するように切り替える。
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
				'navigation-widgets',
			)
		);

		// カスタマイザーの選択的更新のサポートを追加する。
		add_theme_support( 'customize-selective-refresh-widgets' );

		// 全幅と幅広を有効にする。
		add_theme_support( 'align-wide' );

		// ブロックスタイルを有効にする。
		add_theme_support( 'wp-block-styles' );

		// エディタスタイルを有効にする。
		add_theme_support( 'editor-styles' );

		// ブロックエディタ用のスタイルシートを追加するとともにTinyMCEビジュアルエディターにもスタイルシートをリンクできるようにする。
		// 子テーマが使用されている場合、現在の子テーマディレクトリと親テーマディレクトリの両方がテストされ、同じ相対パスを持つ両方のファイルが見つかった場合は、この単一の呼び出しにリンクされます。
		// 参照: https://developer.wordpress.org/reference/functions/add_editor_style/
		add_editor_style( './editor-style.css' );

		if ( is_customize_preview() ) {
			require get_template_directory() . '/includes/starter-content.php';
			add_theme_support( 'starter-content', wpf_get_starter_content() );
		}

		// 埋め込みコンテンツのレスポンシブ対応を有効にする。
		add_theme_support( 'responsive-embeds' );

		// カスタムラインハイトを有効にする。
		add_theme_support( 'custom-line-height' );

		// カスタムリンクカラーを有効にする。
		add_theme_support( 'experimental-link-color' );

		// カバーブロックのカスタムスペーシングを有効にする。
		add_theme_support( 'custom-spacing' );

		// 固定ページで抜粋を有効にする。
		add_post_type_support( 'page', 'excerpt' );

		// 日付アーカイブを404にするオプションを追加する。
		update_option( 'wpf_disable_date_archive', false );

		// 著者アーカイブを404にするオプションを追加する。
		update_option( 'wpf_disable_author_page', false );

		// コメントを完全に無効化するオプションを追加する。
		update_option( 'wpf_disable_comments', false );
	}

	/**
	 * ナビゲーションメニューを登録する。
	 *
	 * @since 0.1.0
	 */
	public static function register_nav_menus() {
		// ナビゲーションメニューを登録する。
		register_nav_menus(
			array(
				'primary'          => __( 'プライマリ', 'wordpressfoundation' ),
				'primary_cta'      => __( 'プライマリCTA', 'wordpressfoundation' ),
				'global_primary'   => __( 'グローバルプライマリ', 'wordpressfoundation' ),
				'global_secondary' => __( 'グローバルセカンダリ', 'wordpressfoundation' ),
				'footer_primary'   => __( 'フッタープライマリ', 'wordpressfoundation' ),
				'footer_secondary' => __( 'フッターセカンダリ', 'wordpressfoundation' ),
				'social_links'     => __( 'ソーシャルリンク', 'wordpressfoundation' ),
			)
		);
	}

	/**
	 * カスタムタクソノミーを登録する。
	 *
	 * @since 0.1.0
	 */
	public static function register_taxonomies() {
		register_taxonomy(
			'blog_cat',
			'blog',
			array(
				'hierarchical'      => true,
				'rewrite'           => array(
					'slug'         => 'blog/category',
					'with_front'   => false,
					'hierarchical' => true,
				),
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'blog_tag',
			'blog',
			array(
				'hierarchical'       => false,
				'rewrite'            => array(
					'slug'       => 'blog/tag',
					'with_front' => false,
				),
				'public'             => true,
				'publicly_queryable' => false,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
			)
		);

		register_taxonomy(
			'project_cat',
			'project',
			array(
				'hierarchical'      => true,
				'rewrite'           => array(
					'slug'         => 'project/category',
					'with_front'   => false,
					'hierarchical' => true,
				),
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'project_domain',
			'project',
			array(
				'label'             => __( '領域', 'wordpressfoundation' ),
				'hierarchical'      => false,
				'rewrite'           => array(
					'slug'       => 'project/domain',
					'with_front' => false,
				),
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
			)
		);

		register_taxonomy(
			'project_tag',
			'project',
			array(
				'hierarchical'       => false,
				'rewrite'            => array(
					'slug'       => 'project/tag',
					'with_front' => false,
				),
				'public'             => true,
				'publicly_queryable' => false,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
			)
		);

		register_taxonomy(
			'area',
			'case-study',
			array(
				'label'             => __( '地域', 'wordpressfoundation' ),
				'hierarchical'      => false,
				'rewrite'           => array(
					'slug'       => 'case-study/area',
					'with_front' => false,
				),
				'public'            => true,
				'show_in_rest'      => true,
				'show_admin_column' => true,
			)
		);

		register_taxonomy(
			'case_study_tag',
			'case-study',
			array(
				'hierarchical'       => false,
				'rewrite'            => array(
					'slug'       => 'case-study/tag',
					'with_front' => false,
				),
				'public'             => true,
				'publicly_queryable' => false,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
			)
		);

		register_taxonomy(
			'member_cat',
			'member',
			array(
				'label'              => __( '肩書き', 'wordpressfoundation' ),
				'hierarchical'       => true,
				'rewrite'            => array(
					'slug'         => 'member/category',
					'with_front'   => false,
					'hierarchical' => true,
				),
				'public'             => true,
				'publicly_queryable' => false,
				'show_ui'            => true,
				'show_admin_column'  => true,
				'show_in_rest'       => true,
			)
		);

		register_taxonomy(
			'member_tag',
			'member',
			array(
				'hierarchical'       => false,
				'rewrite'            => array(
					'slug'       => 'member/tag',
					'with_front' => false,
				),
				'public'             => true,
				'publicly_queryable' => false,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
			)
		);

		register_taxonomy(
			'glossary_cat',
			'glossary',
			array(
				'hierarchical'      => true,
				'rewrite'           => array(
					'slug'         => 'glossary/category',
					'with_front'   => false,
					'hierarchical' => true,
				),
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'glossary_tag',
			'glossary',
			array(
				'hierarchical'       => false,
				'rewrite'            => array(
					'slug'       => 'glossary/tag',
					'with_front' => false,
				),
				'public'             => true,
				'publicly_queryable' => false,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
			)
		);

		register_taxonomy(
			'career_cat',
			'career',
			array(
				'hierarchical'      => true,
				'rewrite'           => array(
					'slug'         => 'career/category',
					'with_front'   => false,
					'hierarchical' => true,
				),
				'public'            => true,
				'show_ui'           => true,
				'show_admin_column' => true,
				'show_in_rest'      => true,
			)
		);

		register_taxonomy(
			'career_tag',
			'career',
			array(
				'hierarchical'       => false,
				'rewrite'            => array(
					'slug'       => 'career/tag',
					'with_front' => false,
				),
				'public'             => true,
				'publicly_queryable' => false,
				'show_in_rest'       => true,
				'show_admin_column'  => true,
			)
		);
	}

	/**
	 * カスタム投稿タイプを登録する。
	 *
	 * @since 0.1.0
	 */
	public static function register_post_types() {
		register_post_type(
			'blog',
			array(
				'labels'        => array(
					'name'          => _x( 'ブログ', 'blog', 'wordpressfoundation' ),
					'singular_name' => _x( 'ブログ', 'blog', 'wordpressfoundation' ),
				),
				'public'        => true,
				'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'trackbacks', 'custom-fields', 'comments', 'page-attributes' ),
				'menu_position' => 5,
				'rewrite'       => array(
					'with_front' => false,
				),
				'has_archive'   => true,
				'taxonomies'    => array( 'blog_cat', 'blog_tag' ),
				'show_in_rest'  => true,
				'wpf_cptp'      => array(
					'permalink_structure' => '/%post_id%/',
					'author_archive'      => false,
				),
			)
		);

		register_post_type(
			'project',
			array(
				'labels'        => array(
					'name'          => _x( '研究・活動', 'project', 'wordpressfoundation' ),
					'singular_name' => _x( '研究・活動', 'project', 'wordpressfoundation' ),
				),
				'public'        => true,
				'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes' ),
				'menu_position' => 5,
				'rewrite'       => array(
					'slug'       => 'projects',
					'with_front' => false,
				),
				'has_archive'   => true,
				'taxonomies'    => array( 'project_cat', 'project_domain', 'project_tag' ),
				'show_in_rest'  => true,
				'wpf_cptp'      => array(
					'permalink_structure' => '/%postname%/',
					'author_archive'      => false,
				),
			)
		);

		register_post_type(
			'case-study',
			array(
				'labels'        => array(
					'name'          => _x( '実践事例', 'case-study', 'wordpressfoundation' ),
					'singular_name' => _x( '実践事例', 'case-study', 'wordpressfoundation' ),
				),
				'public'        => true,
				'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes' ),
				'menu_position' => 5,
				'rewrite'       => array(
					'slug'       => 'case-studies',
					'with_front' => false,
				),
				'has_archive'   => true,
				'taxonomies'    => array( 'area', 'case_study_tag' ),
				'show_in_rest'  => true,
				'wpf_cptp'      => array(
					'permalink_structure' => '/%post_id%/',
					'author_archive'      => false,
				),
			)
		);

		register_post_type(
			'member',
			array(
				'labels'        => array(
					'name'          => _x( 'メンバー', 'member', 'wordpressfoundation' ),
					'singular_name' => _x( 'メンバー', 'member', 'wordpressfoundation' ),
				),
				'public'        => true,
				'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes' ),
				'menu_position' => 5,
				'rewrite'       => array(
					'slug'       => 'members',
					'with_front' => false,
				),
				'has_archive'   => true,
				'taxonomies'    => array( 'member_cat', 'member_tag' ),
				'show_in_rest'  => true,
				'wpf_cptp'      => array(
					'permalink_structure' => '/%postname%/',
					'author_archive'      => false,
				),
			)
		);

		register_post_type(
			'glossary',
			array(
				'labels'        => array(
					'name'          => _x( '用語集', 'glossary', 'wordpressfoundation' ),
					'singular_name' => _x( '用語集', 'glossary', 'wordpressfoundation' ),
				),
				'public'        => true,
				'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes' ),
				'menu_position' => 5,
				'rewrite'       => array(
					'slug'       => 'glossarys',
					'with_front' => false,
				),
				'has_archive'   => true,
				'taxonomies'    => array( 'glossary_cat', 'glossary_tag' ),
				'show_in_rest'  => true,
				'wpf_cptp'      => array(
					'permalink_structure' => '/%postname%/',
					'author_archive'      => false,
				),
			)
		);

		register_post_type(
			'career',
			array(
				'labels'        => array(
					'name'          => _x( '採用情報', 'career', 'wordpressfoundation' ),
					'singular_name' => _x( '採用情報', 'career', 'wordpressfoundation' ),
				),
				'public'        => true,
				'supports'      => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'custom-fields', 'page-attributes' ),
				'menu_position' => 5,
				'rewrite'       => array(
					'slug'       => 'careers',
					'with_front' => false,
				),
				'has_archive'   => true,
				'taxonomies'    => array( 'career_cat', 'career_tag' ),
				'show_in_rest'  => true,
				'wpf_cptp'      => array(
					'permalink_structure' => '/%postname%/',
					'author_archive'      => false,
				),
			)
		);
	}

	/**
	 * カスタム投稿メタを追加する。
	 *
	 * 投稿タイプごとに設定値を変更する場合、
	 * それぞれ register_meta しなければならないため、
	 * 柔軟に設定できるラッパーを定義している。
	 *
	 * @since 0.1.0
	 */
	public static function register_meta() {
		$obj = array();

		// `attachment` 以外のパブリック投稿タイプを取得
		$post_types = get_post_types(
			array(
				'public' => true,
			)
		);
		unset( $post_types['attachment'] );

		// `_wpf_subtitle` メタをパブリック投稿タイプに登録
		$object_type = 'post';
		$data        = array( $object_type => array() );
		foreach ( $post_types as $post_type ) {
			$data[ $object_type ][] = array(
				'object_subtype' => $post_type,
				'type'           => 'string',
				'default'        => '',
				'single'         => true,
				'show_in_rest'   => true,
				'auth_callback'  => function() {
					return current_user_can( 'edit_posts' );
				},
			);
		}
		$obj['_wpf_subtitle'] = $data;

		// `_wpf_show_toc` メタをパブリック投稿タイプに登録
		$object_type = 'post';
		$data        = array( $object_type => array() );
		foreach ( $post_types as $post_type ) {
			if ( in_array( $post_type, array( 'post', 'blog', 'case-study' ), true ) ) {
				$default = false;
				if ( 'case-study' === $post_type ) {
					$default = true;
				}

				$data[ $object_type ][] = array(
					'object_subtype' => $post_type,
					'type'           => 'boolean',
					'default'        => $default,
					'single'         => true,
					'show_in_rest'   => true,
					'auth_callback'  => function() {
						return current_user_can( 'edit_posts' );
					},
				);
			}
		}
		$obj['_wpf_show_toc'] = $data;

		// `_wpf_hide_search_engine` メタをパブリック投稿タイプに登録
		$object_type = 'post';
		$data        = array( $object_type => array() );
		foreach ( $post_types as $post_type ) {
			$data[ $object_type ][] = array(
				'object_subtype' => $post_type,
				'type'           => 'boolean',
				'default'        => false,
				'single'         => true,
				'show_in_rest'   => true,
				'auth_callback'  => function() {
					return current_user_can( 'edit_posts' );
				},
			);
		}
		$obj['_wpf_hide_search_engine'] = $data;

		// `_wpf_schema_type` メタをパブリック投稿タイプに登録
		$object_type = 'post';
		$data        = array( $object_type => array() );
		foreach ( $post_types as $post_type ) {
			$data[ $object_type ][] = array(
				'object_subtype' => $post_type,
				'type'           => 'string',
				'default'        => '',
				'single'         => true,
				'show_in_rest'   => true,
				'auth_callback'  => function() {
					return current_user_can( 'edit_posts' );
				},
			);
		}
		$obj['_wpf_schema_type'] = $data;

		// スキーマにおける会社情報フィールドのメタをパブリック投稿タイプに登録
		$company_fields = array(
			'foundingDate',
			'streetAddress',
			'addressLocality',
			'addressRegion',
			'postalCode',
			'addressCountry',
		);
		foreach ( $company_fields as $field ) {
			$object_type = 'post';
			$data        = array( $object_type => array() );
			foreach ( $post_types as $post_type ) {
				$data[ $object_type ][] = array(
					'object_subtype' => $post_type,
					'type'           => 'string',
					'default'        => '',
					'single'         => true,
					'show_in_rest'   => true,
					'auth_callback'  => function() {
						return current_user_can( 'edit_posts' );
					},
				);
			}
			$obj[ '_wpf_company_' . $field ] = $data;
		}

		// スキーマにおける連絡先情報フィールドのメタをパブリック投稿タイプに登録
		$company_fields = array(
			'telephone',
			'email',
			'contactType',
			'availableLanguage',
			'hoursAvailable',
		);
		foreach ( $company_fields as $field ) {
			$object_type = 'post';
			$data        = array( $object_type => array() );
			foreach ( $post_types as $post_type ) {
				$data[ $object_type ][] = array(
					'object_subtype' => $post_type,
					'type'           => 'string',
					'default'        => '',
					'single'         => true,
					'show_in_rest'   => true,
					'auth_callback'  => function() {
						return current_user_can( 'edit_posts' );
					},
				);
			}
			$obj[ '_wpf_contact_' . $field ] = $data;
		}

		// `_wpf_pickup_flag` メタをパブリック投稿タイプに登録
		$object_type = 'post';
		$data        = array( $object_type => array() );
		foreach ( $post_types as $post_type ) {
			$data[ $object_type ][] = array(
				'object_subtype' => $post_type,
				'type'           => 'boolean',
				'default'        => false,
				'single'         => true,
				'show_in_rest'   => true,
				'auth_callback'  => function() {
					return current_user_can( 'edit_posts' );
				},
			);
		}
		$obj['_wpf_pickup_flag'] = $data;

		// `_wpf_related_members` メタをパブリック投稿タイプに登録
		$object_type = 'post';
		$data        = array( $object_type => array() );
		foreach ( $post_types as $post_type ) {
			$data[ $object_type ][] = array(
				'object_subtype' => $post_type,
				'type'           => 'array',
				'default'        => '',
				'single'         => true,
				'show_in_rest'   => array(
					'schema' => array(
						'type'  => 'array',
						'items' => array(
							'type' => 'integer',
						),
					),
				),
				'auth_callback'  => function() {
					return current_user_can( 'edit_posts' );
				},
			);
		}
		$obj['_wpf_related_members'] = $data;

		// `_wpf_cover_media_id` メタをパブリック投稿タイプに登録
		$object_type = 'post';
		$data        = array( $object_type => array() );
		foreach ( $post_types as $post_type ) {
			$data[ $object_type ][] = array(
				'object_subtype' => $post_type,
				'type'           => 'integer',
				'default'        => 0,
				'single'         => true,
				'show_in_rest'   => true,
				'auth_callback'  => function() {
					return current_user_can( 'edit_posts' );
				},
			);
		}
		$obj['_wpf_cover_media_id'] = $data;

		// `_wpf_cover_media_metadata` メタをパブリック投稿タイプに登録
		$object_type = 'post';
		$data        = array( $object_type => array() );
		foreach ( $post_types as $post_type ) {
			$data[ $object_type ][] = array(
				'object_subtype' => $post_type,
				'type'           => 'object',
				'default'        => (object) array(),
				'single'         => true,
				'show_in_rest'   => array(
					'schema' => array(
						'type'       => 'object',
						'properties' => array(
							'type' => array(
								'type' => 'string',
							),
							'mime' => array(
								'type' => 'string',
							),
							'url'  => array(
								'type' => 'string',
							),
						),
					),
				),
				'auth_callback'  => function() {
					return current_user_can( 'edit_posts' );
				},
			);
		}
		$obj['_wpf_cover_media_metadata'] = $data;

		foreach ( $obj as $key => $value ) {
			foreach ( $value as $object_type => $settings ) {
				foreach ( $settings as $setting ) {
					register_meta( $object_type, $key, $setting );
				}
			}
		}
	}

	/**
	 * 「投稿」のラベル名を変更する。
	 *
	 * @since 0.1.0
	 */
	public static function change_default_post_type_labels() {
		$newlabel               = __( 'ニュース', 'wordpressfoundation' );
		$labels                 = get_post_type_object( 'post' )->labels;
		$labels->name           = $newlabel;
		$labels->singular_name  = $newlabel;
		$labels->new_item       = $newlabel;
		$labels->menu_name      = $newlabel;
		$labels->name_admin_bar = $newlabel;
	}

	/**
	 * ウィジェットを登録する。
	 *
	 * @since 0.1.0
	 */
	public static function register_sidebar() {
		register_sidebar(
			array(
				'name'          => 'サイトフッター',
				'id'            => 'site-footer-widget',
				'description'   => 'サイトフッターにウィジェットを追加します。',
				'before_widget' => '<div class="%2$s">',
				'after_widget'  => '</div>',
			)
		);
	}

	/**
	 * WPが出力するバージョン情報を削除する。
	 *
	 * @since 0.1.0
	 */
	public static function remove_wp_generator() {
		remove_action( 'wp_head', 'wp_generator' );
	}

	/**
	 * メインクエリのカスタマイズ
	 *
	 * @param WP_Query $query WPクエリオブジェクト
	 * @return void
	 */
	public function custom_main_query( $query ) {
		// member投稿タイプの場合
		if ( ! is_admin() && $query->is_main_query() && is_post_type_archive( 'member' ) ) {
			$query->set(
				'orderby',
				array(
					'menu_order' => 'ASC',
					'name'       => 'ASC',
				)
			);
			$query->set( 'posts_per_page', -1 );
		}

		// 著者アーカイブページの場合
		if ( ! is_admin() && $query->is_main_query() && is_author() ) {
			$query->set( 'post_type', array( 'post', 'blog' ) );
		}
	}

	/**
	 * モバイルにおける1ページあたりの表示件数を設定する。
	 *
	 * @since 0.1.0
	 * @param WP_Query $query WPクエリ
	 */
	public static function set_posts_per_page_on_mobile( $query ) {
		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$posts_per_page_on_mobile = get_option( 'posts_per_page_on_mobile' );

		if ( false !== $posts_per_page_on_mobile ) {
			$posts_per_page_on_mobile = (int) $posts_per_page_on_mobile;
		} else {
			return;
		}

		if ( wp_is_mobile() && $posts_per_page_on_mobile > 0 ) {
			$query->set( 'posts_per_page', $posts_per_page_on_mobile );
		}
	}

	/**
	 * フロントエンドにスタイルシートを追加する。
	 *
	 * @since 0.1.0
	 */
	public static function enqueue_styles() {
		wp_enqueue_style(
			'wpf-style',
			get_template_directory_uri() . '/style.css',
			array(),
			filemtime( get_template_directory() . '/style.css' )
		);
	}

	/**
	 * フロントエンドにスクリプトを追加する。
	 *
	 * @since 0.1.0
	 */
	public static function enqueue_scripts() {
		wp_enqueue_script(
			'wpf-prism',
			get_template_directory_uri() . '/assets/js/vendor/prism.js',
			array(),
			filemtime( get_template_directory() . '/assets/js/vendor/prism.js' ),
			true
		);

		wp_enqueue_script(
			'wpf-main',
			get_template_directory_uri() . '/assets/js/main.js',
			array( 'wp-i18n' ),
			filemtime( get_template_directory() . '/assets/js/main.js' ),
			true
		);

		// JSから翻訳ファイルを参照できるようにする。
		wp_set_script_translations(
			'wpf-main',
			'wordpressfoundation',
			get_template_directory() . '/languages'
		);
	}

	/**
	 * ブロックエディタにスクリプトを追加する。
	 *
	 * @since 0.1.0
	 */
	public static function enqueue_block_editor_scripts() {
		wp_enqueue_script(
			'wpf-editor',
			get_template_directory_uri() . '/assets/js/editor.js',
			array(),
			filemtime( get_template_directory() . '/assets/js/editor.js' ),
			true
		);

		wp_enqueue_script(
			'wpf-inspector',
			get_template_directory_uri() . '/assets/js/inspector.js',
			array( 'lodash' ),
			filemtime( get_template_directory() . '/assets/js/inspector.js' ),
			true
		);
	}

	/**
	 * 管理画面をカスタマイズする。
	 *
	 * @since 0.1.0
	 */
	public static function add_settings() {
		// 一般設定に「サイトの所有者」を追加する。
		add_settings_field(
			'site_owner', // 項目識別子
			'サイトの所有者', // ラベル文言
			function( $args ) {
				echo '<input type="text" id="' . esc_attr( $args[0] ) . '" name="' . esc_attr( $args[0] ) . '" value="' . esc_attr( get_option( $args[0] ) ) . '" /><p class="description" id="tagline-description">著作権表示の所有者名に使用します。サイトの所有者がサイトタイトルと異なる場合は入力します。</p>';
			},
			'general', // 表示するページ
			'default', // 表示するセクション
			array( // $args
				'site_owner', // 項目識別子と一致すること
			)
		);
		register_setting(
			'general',
			'site_owner',
			array(
				'type'         => 'string',
				'show_in_rest' => array(
					'schema' => array(
						'type' => 'string',
					),
				),
			)
		);

		// 表示設定に「モバイルでの1ページあたりの表示件数」を追加する。
		add_settings_field(
			'posts_per_page_on_mobile', // 項目識別子
			'1ページに表示する最大投稿数（モバイル）', // ラベル文言
			function( $args ) {
				echo '<input type="number" step="1" min="0" id="' . esc_attr( $args[0] ) . '" name="' . esc_attr( $args[0] ) . '" value="' . esc_attr( get_option( $args[0] ) ) . '" class="small-text" /> 件<p class="description" id="tagline-description">モバイルでの1ページあたりの投稿数を変更する場合は設定します。「0」の場合は、デフォルトと同じ値に設定します。</p>';
			},
			'reading', // 表示するページ
			'default', // 表示するセクション
			array( // $args
				'posts_per_page_on_mobile', // 項目識別子と一致すること
			)
		);
		register_setting(
			'reading',
			'posts_per_page_on_mobile',
			array(
				'type'         => 'integer',
				'show_in_rest' => array(
					'schema' => array(
						'type' => 'integer',
					),
				),
			)
		);
	}

	/**
	 * ソーシャルリンクナビゲーションのメニューテキストをSVGアイコンに置換する。
	 *
	 * リンクURLのドメインに該当するソーシャルアイコンがあれば置換する。なければ何もしない。
	 *
	 * @since 0.1.0
	 * @param string   $item_output メニューアイテムの開始時のHTML出力。
	 * @param WP_Post  $item        メニューアイテムのデータオブジェクト。
	 * @param int      $depth       メニューの深さ。パディングに使用する。
	 * @param stdClass $args        wp_nav_menu()の引数のオブジェクト。
	 * @return string ソーシャルアイコンでメニュー項目を出力する。
	 */
	public static function replace_to_social_icon( $item_output, $item, $depth, $args ) {
		if ( 'social_links' === $args->theme_location ) {
			$svg = WPF_Icons::get_social_link_svg( $item->url );

			if ( ! empty( $svg ) ) {
				$item_output = str_replace( $args->link_before, $svg, $item_output );
			}
		}

		return $item_output;
	}

	/**
	 * ナビゲーションメニューのアンカー要素にクラス属性を設定できるようにする
	 *
	 * @since 0.1.0
	 * @param array    $atts メニューアイテムの<a>要素に適用されるHTML属性。空の文字列は無視される
	 * @param WP_Post  $item 現在のメニュー項目
	 * @param stdClass $args wp_nav_menu()の引数のオブジェクト
	 * @return array
	 */
	public static function set_nav_menu_link_classes( $atts, $item, $args ) {
		if ( isset( $args->wpf_link_classes ) && $args->wpf_link_classes ) {
			$atts['class'] = $args->wpf_link_classes;
		}
		return $atts;
	}

	/**
	 * CPT投稿用ページでもメニュー項目にカレントクラスを追加
	 *
	 * @param string[] $classes メニューアイテムの<li>要素に適用されるCSSクラスの配列
	 * @param WP_Post  $menu_item 現在のメニュー項目オブジェクト
	 * @param stdClass $args wp_nav_menu() の引数のオブジェクト
	 * @param int      $depth メニュー項目の深さ。パディングに使用する
	 * @return string[]
	 */
	public static function nav_menu_item_current_class( $classes, $menu_item, $args, $depth ) {
		$post_type         = get_post_type();
		$current_object_id = WPF_Utils::get_page_for_posts();
		if ( $current_object_id === (int) $menu_item->object_id ||
			'member' === $post_type && str_ends_with( $menu_item->url, '/people/' )
		) {
			array_push( $classes, 'current-menu-item' );
		}
		return $classes;
	}

	/**
	 * CPT投稿用ページでも aria-current="page" を適用
	 * CPT投稿用ページの場合、WordPressデフォルトの aria-current="page" 付与が機能しないため、フックする。
	 *
	 * @param array    $atts メニュー項目のa要素に適用されるHTML属性、空文字列は無視される
	 * @param WP_Post  $item 現在のメニュー項目オブジェクト
	 * @param stdClass $args wp_nav_menu() の引数のオブジェクト
	 * @return array
	 */
	public static function nav_menu_item_aria_current( $atts, $item, $args ) {
		$post_type         = get_post_type();
		$current_object_id = WPF_Utils::get_page_for_posts();
		if ( empty( $atts['aria-current'] ) &&
			(
				$current_object_id === (int) $item->object_id ||
				'member' === $post_type && str_ends_with( $item->url, '/people/' )
			)
		) {
			$atts['aria-current'] = 'page';
		}
		return $atts;
	}

	/**
	 * 特定のアンカー（ #menu ）が付与されたナビゲーションメニューのキーボードフォーカスを無効にする。
	 *
	 * @since 0.1.0
	 * @param array    $atts メニューアイテムの <a> 要素に適用されるHTML属性。空の文字列は無視される。
	 * @param WP_Post  $item 現在のメニュー項目。
	 * @param stdClass $args wp_nav_menu() の引数のオブジェクト。
	 * @return array
	 */
	public static function disable_keyboard_focus_with_specific_anchor( $atts, $item, $args ) {
		if ( '#menu' === $atts['href'] ) {
			$atts['tabindex'] = '-1';
		}
		return $atts;
	}

	/**
	 * ページメニューのアンカー要素にクラス属性を設定できるようにする。
	 *
	 * @since 0.1.0
	 * @param array   $atts メニュー項目のアンカー要素に適用されるHTML属性。空文字列は無視される。
	 * @param WP_Post $page ページデータオブジェクト。
	 * @param int     $depth ページの深さ。パディングに使用される。
	 * @param array   $args 引数の配列。
	 * @param int     $current_page_id 現在のページのID。
	 * @return array
	 */
	public static function set_page_menu_link_classes( $atts, $page, $depth, $args, $current_page_id ) {
		if ( isset( $args['wpf_link_classes'] ) && $args['wpf_link_classes'] ) {
			$atts['class'] = $args['wpf_link_classes'];
		}
		return $atts;
	}

	/**
	 * 抜粋の接尾辞を変更する。
	 *
	 * [...] => ...
	 *
	 * @since 0.1.0
	 * @param string $more デフォルトの接尾辞。
	 * @return string 新しい接尾辞を返す。
	 */
	public static function excerpt_more( $more ) {
		return '…';
	}

	/**
	 * 個別投稿の改ページを番号付きページネーションにする。
	 *
	 * wp_link_page の next_or_number オプションは number かそれ以外（ next ）しかないため、
	 * next_and_number という独自の値を指定した場合は、ページ番号リンクと矢印リンクの両方を表示する。
	 *
	 * @link https://developer.wordpress.org/reference/functions/wp_link_pages/
	 *
	 * @since 0.1.0
	 * @param array $args wp_link_pages の引数。
	 * @return array
	 */
	public static function single_paginate_link( $args ) {
		global $page, $numpages, $more, $pagenow;

		if ( 'next_and_number' !== $args['next_or_number'] ) {
			return $args;
		}

		$args['next_or_number'] = 'number';
		if ( ! $more ) {
			return $args;
		}

		if ( $page - 1 ) { // 前のページがある場合
			$args['before'] .= _wp_link_page( $page - 1 )
			. $args['link_before'] . $args['previouspagelink'] . $args['link_after'] . '</a>';
		}

		if ( $page < $numpages ) { // 次のページがある場合
			$args['after'] = _wp_link_page( $page + 1 )
			. $args['link_before'] . ' ' . $args['nextpagelink'] . $args['link_after'] . '</a>'
			. $args['after'];
		}

		return $args;
	}

	/**
	 * WPが出力するアーカイブタイトルの接頭辞を削除する。
	 *
	 * @since 0.1.0
	 * @param string $prefix 接頭辞の文字列。
	 * @return string
	 */
	public static function remove_archive_title_prefix( $prefix ) {
		return '';
	}

	/**
	 * リビジョンの保存数を変更する。
	 *
	 * @since 0.1.0
	 * @param int     $num 保存するリビジョン数。
	 * @param WP_Post $post 投稿オブジェクト。
	 * @return int
	 */
	public static function revisions_to_keep( $num, $post ) {
		return 3;
	}

	/**
	 * 投稿一覧にサムネイル列を追加する。
	 *
	 * @since 0.1.0
	 * @param string $column 表示するカラム名。
	 * @param int    $post_id 現在の投稿ID。
	 */
	public static function add_thumbnail_column( $column, $post_id ) {
		switch ( $column ) {
			// 行のキーが thumb なら アイキャッチ を出力
			case 'thumb':
				$thumb = get_the_post_thumbnail( $post_id, array( 80, 80 ) );
				// アイキャッチがある場合
				if ( ! empty( $thumb ) ) {
					// 編集権限、ゴミ箱内かどうかの判別用変数
					$user_can_edit = current_user_can( 'edit_post', $post_id );
					$is_trash      = isset( $_REQUEST['status'] ) && 'trash' === $_REQUEST['status']; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
					// 編集権限があり、ゴミ箱でないなら画像をリンクつきに
					if ( ! $is_trash || $user_can_edit ) {
						$thumb = sprintf(
							'<a href="%s" title="%s">%s</a>',
							get_edit_post_link( $post_id, true ),
							/* translators: %s: 投稿タイトル */
							esc_attr( sprintf( __( 'Edit &#8220;%s&#8221;', 'wordpressfoundation' ), _draft_or_post_title() ) ),
							$thumb
						);
					}
					// 出力
					echo $thumb; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				}
				break;
			default:
				break;
		}
	}

	/**
	 * 投稿一覧のサムネイル列をスタイリングする。
	 *
	 * @since 0.1.0
	 * @param array $columns 投稿リストのカラム。
	 */
	public static function add_thumbnail_column_style( $columns ) {
		echo '<style>.column-thumb{width:80px;}</style>';

		// サムネイルのラベルを先頭のカラムに追加する。
		$columns          = array_reverse( $columns, true );
		$columns['thumb'] = '<div class="dashicons dashicons-format-image"></div>';
		$columns          = array_reverse( $columns, true );

		return $columns;
	}

	/**
	 * タームを検索対象に含める。
	 * ターム名、タームスラッグ、タームデスクリプションが対象。
	 * なお、子タームの場合は祖先、親も対象となる。
	 *
	 * @since 0.1.0
	 * @param string   $search WHERE句で使用される検索SQL。
	 * @param WP_Query $wp_query 現在の WP_Query オブジェクト。
	 * @return string
	 */
	public static function search_include_terms( $search, $wp_query ) {
		global $wpdb;

		if ( ! $wp_query->is_search ) {
			return $search;
		}

		if ( ! isset( $wp_query->query_vars ) ) {
			return $search;
		}

		// 検索キーワードを分割して処理
		$search_words = explode( ' ', isset( $wp_query->query_vars['s'] ) ? $wp_query->query_vars['s'] : '' );
		if ( count( $search_words ) > 0 ) {
			$search = '';
			foreach ( $search_words as $word ) {
				if ( ! empty( $word ) ) {
					$search_word = $wpdb->esc_like( $word );
					$search_word = '%' . $search_word . '%';
					$search     .= $wpdb->prepare(
						" AND (
                            {$wpdb->posts}.post_title LIKE %s
                            OR {$wpdb->posts}.post_content LIKE %s
                            OR {$wpdb->posts}.ID IN (
                                SELECT distinct r.object_id
                                FROM {$wpdb->term_relationships} AS r
                                INNER JOIN {$wpdb->term_taxonomy} AS tt ON r.term_taxonomy_id = tt.term_taxonomy_id
                                INNER JOIN {$wpdb->terms} AS t ON tt.term_id = t.term_id
                                WHERE t.name LIKE %s
                                OR t.slug LIKE %s
                                OR tt.description LIKE %s
                                OR tt.parent IN (
                                    SELECT parent_tt.term_taxonomy_id
                                    FROM {$wpdb->term_taxonomy} AS parent_tt
                                    INNER JOIN {$wpdb->terms} AS parent_t ON parent_tt.term_id = parent_t.term_id
                                    WHERE parent_t.name LIKE %s
                                    OR parent_t.slug LIKE %s
                                    OR parent_tt.description LIKE %s
                                )
                            )
                        ) ",
						$search_word,
						$search_word,
						$search_word,
						$search_word,
						$search_word,
						$search_word,
						$search_word,
						$search_word
					);
				}
			}
		}

		return $search;
	}

	/**
	 * テストフレンドリーな wp_safe_redirect 関数。
	 *
	 * @param string $url リダイレクト先のURL。
	 */
	protected function safe_redirect( $url ) {
		wp_safe_redirect( $url );
	}

	/**
	 * テストフレンドリーな exit 関数。
	 *
	 * @param int $code ステータスコード。
	 */
	protected function terminate( $code = 0 ) {
        exit( $code ); // phpcs:ignore
	}

	/**
	 * コメント管理画面へのアクセスを拒否する。
	 *
	 * @since 0.1.0
	 */
	public function deny_access_to_comments_admin() {
		global $pagenow;

		// コメント管理画面
		if ( 'edit-comments.php' === $pagenow ) {
			$this->safe_redirect( admin_url() );
			$this->terminate();

			// ディスカッション設定画面
		} elseif ( 'options-discussion.php' === $pagenow ) {
			$this->safe_redirect( admin_url() );
			$this->terminate();
		}
	}

	/**
	 * ダッシュボードからコメントメタボックスを削除する。
	 *
	 * @since 0.1.0
	 */
	public static function remove_comments_meta_box() {
		remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
	}

	/**
	 * 全ての投稿タイプのコメントサポートを無効にする。
	 *
	 * @since 0.1.0
	 */
	public static function disable_all_post_types_comments_support() {
		foreach ( get_post_types() as $post_type ) {
			if ( post_type_supports( $post_type, 'comments' ) ) {
				remove_post_type_support( $post_type, 'comments' );
				remove_post_type_support( $post_type, 'trackbacks' );
			}
		}
	}

	/**
	 * 管理画面からコメントメニューを削除する。
	 *
	 * @since 0.1.0
	 */
	public static function remove_comments_menu_from_admin() {
		remove_menu_page( 'edit-comments.php' );
		remove_submenu_page( 'options-general.php', 'options-discussion.php' );
	}

	/**
	 * 管理バーからコメントメニューを削除する。
	 *
	 * @since 0.1.0
	 */
	public static function remove_comments_menu_from_adminbar() {
		if ( is_admin_bar_showing() ) {
			remove_action( 'admin_bar_menu', 'wp_admin_bar_comments_menu', 60 );
		}
	}

	/**
	 * リクエストヘッダの X-Pingback を無効化する。
	 *
	 * @since 0.1.0
	 * @param string[] $headers 送信するヘッダーの連想配列。
	 * @param WP       $wp 現在のWordPress環境インスタンス。
	 * @return string[]
	 */
	public static function disable_x_pingback( $headers, $wp ) {
		unset( $headers['X-Pingback'] );
		return $headers;
	}

	/**
	 * テストフレンドリーな __return_false 関数。
	 *
	 * @return false
	 */
	public static function return_false() {
		return false;
	}

	/**
	 * テストフレンドリーな __return_empty_array 関数。
	 *
	 * @return array
	 */
	public static function return_empty_array() {
		return array();
	}

	/**
	 * 編集権限のないユーザーからの WP REST API へのアクセスを拒否する。
	 * なお、$whitelist で指定された文字列を含むパスへのリクエストは除く。
	 *
	 * @link https://ja.wp-api.org/guide/authentication/
	 * @link https://wordpress.stackexchange.com/questions/346377/current-user-canadministrator-returns-false-when-im-logged-in
	 *
	 * @since 0.1.0
	 * @param mixed           $result リクエストされたバージョンを置き換えるためのレスポンス。
	 * @param WP_REST_Server  $server サーバーインスタンス。
	 * @param WP_REST_Request $request レスポンスを生成するために使用されるリクエスト。
	 * @return mixed
	 */
	public static function protect_rest_api( $result, $server, $request ) {
		// リクエスト元のクライアントがホワイトリストに含まれる場合、レスポンスを返す。なお、全てのルートは /wp-json で確認できる。
		$whitelist       = array( 'oembed', 'contact-form-7', 'akismet', 'jetpack', 'yoast', 'redirection', 'pll', 'post', 'blog', 'project', 'case-study', 'member', 'glossary', 'career' );
		$requested_route = $request->get_route();
		foreach ( $whitelist as $client ) {
			if ( false !== strpos( $requested_route, $client ) ) {
				return $result;
			}
		}

		// gutenberg エディタでは api 呼び出しが必須であるため、リクエストに nonce を含み、かつ編集権限を持つユーザーなら、レスポンスを返す。
		if ( current_user_can( 'edit_posts' ) || current_user_can( 'edit_pages' ) ) {
			return $result;
		}

		return new WP_Error( 'wpf_rest_forbidden', __( 'Sorry, you are not allowed to do that.', 'wordpressfoundation' ), array( 'status' => rest_authorization_required_code() ) );
	}

	/**
	 * コメントのクラス属性値からユーザー名を特定できないようにする。
	 *
	 * @since 0.1.0
	 * @param string|string[] $classes クラス属性の1つまたは複数のクラス値。
	 * @return string|string[]
	 */
	public static function hide_username_from_comment_class( $classes ) {
		foreach ( $classes as $key => $class ) {
			if ( strstr( $class, 'comment-author-' ) ) {
				unset( $classes[ $key ] );
			}
		}
		return $classes;
	}

	/**
	 * 恒久リダイレクトすべきか判定
	 * テスタビリティのために恒久リダイレクトの実行メソッドから分離している
	 */
	public static function should_redirect_301() {
		return ( is_author() && get_option( 'wpf_disable_author_page' ) ) ||
			( is_date() && get_option( 'wpf_disable_date_archive' ) );
	}

	/**
	 * 恒久リダイレクトの実行
	 */
	public static function redirect_301() {
		if ( self::should_redirect_301() ) {
			wp_safe_redirect( home_url(), 301 );
			exit;
		}
	}

	/**
	 * 特定のページの canonical URL への自動リダイレクトを無効化する。
	 *
	 * /?author=ID が /author/USERNAME/ にリダイレクトすることでユーザー名が露出するのを防ぐ。
	 *
	 * @param string $redirect_url リダイレクトが必要かどうかを判断するために使用される。
	 * @param bool   $requested_url 新しいURLにリダイレクトする。
	 * @return string|false
	 */
	public static function redirect_canonical( $redirect_url, $requested_url ) {
		$match_url = array(
			'?author=',
		);
		foreach ( $match_url as $url ) {
			if ( strpos( $requested_url, $url ) ) {
				return false;
			}
		}
		return $redirect_url;
	}

	/**
	 * ドキュメントタイトルをフィルタリングする。
	 *
	 * @param array $title {
	 *     ドキュメントタイトルパーツ。
	 *
	 *     @type string $title   閲覧しているページのタイトル。
	 *     @type string $page    オプション。ページ付けなら、ページ番号。
	 *     @type string $tagline オプション。ホームページに表示されるサイト説明文。
	 *     @type string $site    オプション。ホームページでない場合のサイトタイトル。
	 * }
	 * @return array
	 */
	public static function filter_document_title_parts( $title ) {
		// 検索結果ページ
		if ( is_search() ) {
			/* translators: 検索キーワード */
			$title['title'] = sprintf( __( '%sの検索結果', 'wordpressfoundation' ), get_search_query() );
		}

		// 投稿用ページ
		if ( is_home() || is_post_type_archive() ) {
			$page_for_posts = WPF_Utils::get_page_for_posts();

			if ( $page_for_posts ) {
				$post_title = get_the_title( $page_for_posts );

				if ( $post_title ) {
					$title['title'] = $post_title;
				}
			}
		}

		return $title;
	}

	/**
	 * canonical URLをフィルタリングする。
	 *
	 * @param string  $canonical_url 現在の投稿のcanonical URL。
	 * @param WP_Post $post          投稿オブジェクト。
	 * @return array
	 */
	public static function filter_get_canonical_url( $canonical_url, $post ) {
		// 検索結果ページ
		if ( is_search() ) {
			// noindexでも、ある程度の体裁は整えておく。
			global $wp_rewrite;
			$permastruct = $wp_rewrite->get_search_permastruct();
			if ( $permastruct ) {
				$canonical_url = home_url( user_trailingslashit( '/' . str_replace( '%search%', get_search_query(), $permastruct ) ) );
			}

			// "素"のCPTアーカイブ（対義はCPT投稿用ページ）
		} elseif ( is_post_type_archive() ) {
			// noindexでも、ある程度の体裁は整えておく。
			$post_type = WPF_Utils::get_post_type();
			if ( ! empty( $post_type ) ) {
				$canonical_url = get_post_type_archive_link( $post_type );
			}

			// 日付アーカイブ
		} elseif ( is_date() ) {
			// noindexでも、ある程度の体裁は整えておく。
			global $wp_rewrite;
			$permastruct = $wp_rewrite->get_date_permastruct();
			if ( $permastruct ) {
				$date          = explode( ' ', str_replace( array( '-', ':' ), ' ', $post->post_date ) );
				$canonical_url = home_url( user_trailingslashit( '/' . str_replace( array( '%year%', '%monthnum%', '%day%' ), array( $date[0], $date[1], $date[2] ), $permastruct ) ) );
			}

			// フロントページ
		} elseif ( is_front_page() ) {
			$canonical_url = home_url();

			// カテゴリアーカイブ
		} elseif ( is_category() ) {
			$term_link = get_term_link( get_queried_object()->term_id );

			if ( $term_link ) {
				$canonical_url = $term_link;
			}

			// タグアーカイブ
		} elseif ( is_tag() ) {
			$term_link = get_term_link( get_queried_object()->term_id );

			if ( $term_link ) {
				$canonical_url = $term_link;
			}

			// ctaxアーカイブ
		} elseif ( is_tax() ) {
			$term_link = get_term_link( get_queried_object()->term_id );

			if ( $term_link ) {
				$canonical_url = $term_link;
			}
		}

		/*
		 * 投稿用ページ（固定ページ管理されているアーカイブページ）
		 */

		$page_for_posts = WPF_Utils::get_page_for_posts();

		if ( $page_for_posts && ( is_home() || is_post_type_archive() ) ) {
			$canonical_url = get_permalink( $page_for_posts );
		}

		return $canonical_url;
	}

	/**
	 * _wpf_hide_search_engineがtrueの投稿をサイトマップから除外する
	 *
	 * @param array  $args WP_Query 引数の配列
	 * @param string $post_type 投稿タイプ名
	 * @return string
	 */
	public static function exclude_posts_from_sitemap( $args, $post_type ) {
		if ( WPF_Template_Tags::has_post_type_with_meta_value( '_wpf_hide_search_engine', $post_type ) ) {
			// 既存のメタクエリ条件を保持
			$meta_query = isset( $args['meta_query'] ) ? $args['meta_query'] : array();

			// _wpf_hide_search_engineがtrueのものを除外する条件を追加
			$meta_query[] = array(
				'relation' => 'OR',
				array(
					'key'     => '_wpf_hide_search_engine',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_wpf_hide_search_engine',
					'value'   => '1',
					'compare' => '!=',
				),
			);

			$args['meta_query'] = $meta_query; // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
		}

		return $args;
	}

	/**
	 * SEO関連の設定を返す。
	 *
	 * @since 0.1.0
	 * @return array
	 */
	public static function get_the_seo_settings() {
		global $post;

		$ogp_image = wp_get_attachment_image_src( get_theme_mod( 'wpf_ogp_image' ), 'full' );

		$seo_settings = array(
			'title'       => wp_get_document_title(),
			'description' => get_bloginfo( 'description' ),
			// 個別投稿ページの正規URLを返す。アーカイブでは最新の投稿のURLを返すので注意。
			// なぜなら、WPが出力する素のアーカイブは単なるアイテムのコレクションという考え
			// 方だから: https://wordpress.stackexchange.com/a/407912/232833
			'canonical'   => wp_get_canonical_url(),
			'image_url'   => $ogp_image ? $ogp_image[0] : '',
			'image_w'     => $ogp_image ? $ogp_image[1] : '',
			'image_h'     => $ogp_image ? $ogp_image[2] : '',
			'locale'      => get_locale(),
			'noindex'     => false,
		);

		/*
		 * noindex
		 */

		// 検索結果ページ。検索スパム対策。
		if ( is_search() ) {
			$seo_settings['noindex'] = true;

			global $wp_query;
			$search_query = get_search_query();
			/* translators: %1$s: 検索キーワード %2$s: 掲載数 */
			$seo_settings['description'] = function_exists( 'pll__' ) ? sprintf( pll__( '「%1$s」に関する記事を%2$s件掲載しています。' ), $search_query, $wp_query->found_posts ) : sprintf( __( '「%1$s」に関する記事を%2$s件掲載しています。', 'wordpressfoundation' ), $search_query, $wp_query->found_posts );

			// "素"のCPTアーカイブ（対義はCPT投稿用ページ）
		} elseif ( is_post_type_archive() ) {
			$seo_settings['noindex'] = true;

			$post_type_object            = get_post_type_object( WPF_Utils::get_post_type() );
			$seo_settings['description'] = function_exists( 'pll__' ) ? pll__( $post_type_object->description ) : $post_type_object->description;

			// 日付アーカイブ
		} elseif ( is_date() ) {
			$seo_settings['noindex'] = true;

			if ( is_year() ) {
				$date = get_the_date( _x( 'Y年', 'yearly archives date format', 'wordpressfoundation' ) );
			} elseif ( is_month() ) {
				$date = get_the_date( _x( 'Y年n月', 'monthly archives date format', 'wordpressfoundation' ) );
			} elseif ( is_day() ) {
				// 年月日は管理画面で日付形式を設定するのがWPの仕様。
				// ポリランを使用している場合は文字列翻訳で年月日の翻訳を定義できる。
				$date = get_the_date();
			}

			global $wp_query;
			/* translators: %1$s: 日付 %2$s: 掲載数 */
			$seo_settings['description'] = function_exists( 'pll__' ) ? sprintf( pll__( '%1$sに公開した記事を%2$s件掲載しています。' ), $date, $wp_query->found_posts ) : sprintf( __( '%1$sに公開した記事を%2$s件掲載しています。', 'wordpressfoundation' ), $date, $wp_query->found_posts );

			// 404エラー
		} elseif ( is_404() ) {
			$seo_settings['noindex'] = true;
		}

		/*
		 * フロントページ
		 */

		if ( is_front_page() ) {
			$seo_settings['noindex'] = false;
		}

		/*
		 * 個別投稿・固定ページ
		 */

		if ( is_singular() ) {
			$seo_settings['description'] = get_the_excerpt();

			if ( has_post_thumbnail() ) {
				$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id(), 'full' );

				$seo_settings['image_url'] = $thumbnail[0];
				$seo_settings['image_w']   = $thumbnail[1];
				$seo_settings['image_h']   = $thumbnail[2];
			}
		}

		/*
		 * taxアーカイブ
		 */

		if ( is_category() || is_tag() || is_tax() ) {
			$seo_settings['description'] = WPF_Utils::get_the_term_description();
		}

		/*
		 * 投稿用ページ（固定ページ管理されているアーカイブページ）
		 */

		$page_for_posts = WPF_Utils::get_page_for_posts();

		if ( 'page' === get_option( 'show_on_front' ) && $page_for_posts && ( is_home() || is_post_type_archive() ) ) {
			$excerpt = get_the_excerpt( $page_for_posts );

			if ( $excerpt ) {
				$seo_settings['description'] = $excerpt;
			}

			if ( has_post_thumbnail( $page_for_posts ) ) {
				$thumbnail = wp_get_attachment_image_src( get_post_thumbnail_id( $page_for_posts ), 'full' );

				$seo_settings['image_url'] = $thumbnail[0];
				$seo_settings['image_w']   = $thumbnail[1];
				$seo_settings['image_h']   = $thumbnail[2];
			}

			$seo_settings['noindex'] = false;
		}

		// _wpf_hide_search_engineを有効にしている場合、noindexを強制
		if ( $post && isset( $post->ID ) ) {
			$force_noindex = get_post_meta( $post->ID, '_wpf_hide_search_engine', true );
			if ( $force_noindex ) {
				$seo_settings['noindex'] = true;
			}
		}

		return $seo_settings;
	}

	/**
	 * SEO関連のHTMLタグを出力する。
	 *
	 * @since 0.1.0
	 */
	public static function the_seo_tag() {
		echo self::get_the_seo_tag();// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * 検索エンジンにインデックスしたくないページに noindex を設定する。
	 *
	 * なお、WP が meta robots を出力しているため、それをフィルタリングする。
	 * サイト全体のインデックスを拒否している場合は nofollow もあわせて設定される。
	 *
	 * @link https://github.com/WordPress/wordpress-develop/blob/5a99ab468a2bae1c4660ec45dde908f3b1032041/src/wp-includes/robots-template.php#L140
	 *
	 * @param array $robots robots ディレクティブの連想配列。
	 * @return array フィルタリング済みの robots ディレクティブ。
	 */
	public static function no_robots( array $robots ) {
		$seo_settings = self::get_the_seo_settings();

		if ( $seo_settings['noindex'] ) {
			return wp_robots_no_robots( $robots );
		}

		return $robots;
	}

	/**
	 * SEO関連のメタタグの出力バッファを返す。
	 *
	 * @since 0.1.0
	 */
	public static function get_the_seo_tag() {
		$seo_settings = self::get_the_seo_settings();

		ob_start();

		if ( ! empty( $seo_settings['canonical'] ) ) {
			printf(
				'<link rel="canonical" href="%s" />' . "\n",
				esc_attr( $seo_settings['canonical'] )
			);
		}

		printf(
			'<meta name="description" content="%s" />' . "\n",
			esc_attr( $seo_settings['description'] )
		);
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
		printf(
			'<meta name="twitter:title" content="%s">' . "\n",
			esc_attr( $seo_settings['title'] )
		);
		printf(
			'<meta name="twitter:description" content="%s">' . "\n",
			esc_attr( $seo_settings['description'] )
		);

		if ( ! empty( $seo_settings['image_url'] ) ) {
			printf(
				'<meta name="twitter:image" content="%s">' . "\n",
				esc_attr( $seo_settings['image_url'] )
			);
		}

		printf(
			'<meta property="og:locale" content="%s" />' . "\n",
			esc_attr( $seo_settings['locale'] )
		);
		echo '<meta property="og:type" content="website" />' . "\n";
		printf(
			'<meta property="og:site_name" content="%s" />' . "\n",
			esc_attr( get_bloginfo( 'name' ) )
		);
		printf(
			'<meta property="og:title" content="%s" />' . "\n",
			esc_attr( $seo_settings['title'] )
		);
		printf(
			'<meta property="og:description" content="%s" />' . "\n",
			esc_attr( $seo_settings['description'] )
		);
		printf(
			'<meta property="og:url" content="%s" />' . "\n",
			esc_url( $seo_settings['canonical'] )
		);

		if ( ! empty( $seo_settings['image_url'] ) ) {
			printf(
				'<meta property="og:image" content="%s" />' . "\n",
				esc_url( $seo_settings['image_url'] )
			);
			printf(
				'<meta property="og:image:width" content="%s">' . "\n",
				esc_attr( $seo_settings['image_w'] )
			);
			printf(
				'<meta property="og:image:height" content="%s">' . "\n",
				esc_attr( $seo_settings['image_h'] )
			);
		}

		return ob_get_clean();
	}

	/**
	 * スキーマタグを追加
	 */
	public static function add_schema() {
		if ( is_404() ) {
			return;
		}

		global $post;

		$schema_type = '';

		// スキーマタイプを取得
		if ( isset( $post ) && isset( $post->ID ) ) {
			$schema_type = get_post_meta( $post->ID, '_wpf_schema_type', true );
		}

		// スキーマタイプが未設定の場合、クエリタイプに応じてデフォルト値を設定
		if ( empty( $schema_type ) ) {
			if ( is_front_page() ) {
				$schema_type = 'WebSite';
			} elseif ( is_author() ) {
				$schema_type = 'ProfilePage';
			} elseif ( is_home() || is_archive() ) {
				$schema_type = 'CollectionPage';
			} elseif ( is_single() ) {
				$schema_type = 'Article';
			}
		}

		// スキーマインスタンスを生成
		$schema = null;

		switch ( $schema_type ) {
			case 'AboutPage':
				$schema = new WPF_About_Page_Schema();
				$schema->set_company_info(
					array(
						'foundingDate'    => get_post_meta( $post->ID, '_wpf_company_foundingDate', true ),
						'streetAddress'   => get_post_meta( $post->ID, '_wpf_company_streetAddress', true ),
						'addressLocality' => get_post_meta( $post->ID, '_wpf_company_addressLocality', true ),
						'addressRegion'   => get_post_meta( $post->ID, '_wpf_company_addressRegion', true ),
						'postalCode'      => get_post_meta( $post->ID, '_wpf_company_postalCode', true ),
						'addressCountry'  => get_post_meta( $post->ID, '_wpf_company_addressCountry', true ),
					)
				);
				break;

			case 'ContactPage':
				$schema = new WPF_Contact_Page_Schema();
				$schema->set_contact_info(
					array(
						'telephone'         => get_post_meta( $post->ID, '_wpf_contact_telephone', true ),
						'email'             => get_post_meta( $post->ID, '_wpf_contact_email', true ),
						'contactType'       => get_post_meta( $post->ID, '_wpf_contact_contactType', true ),
						'availableLanguage' => get_post_meta( $post->ID, '_wpf_contact_availableLanguage', true ),
						'hoursAvailable'    => get_post_meta( $post->ID, '_wpf_contact_hoursAvailable', true ),
					)
				);
				break;

			case 'NewsArticle':
				$schema = new WPF_News_Article_Schema();
				break;

			case 'BlogPosting':
				$schema = new WPF_Blog_Posting_Schema();
				break;

			case 'CollectionPage':
				$schema = new WPF_Collection_Page_Schema();
				break;

			case 'WebSite':
				$schema = new WPF_Web_Site_Schema();
				break;

			case 'Article':
				$schema = new WPF_Article_Schema();
				break;

			case 'ProfilePage':
				$schema = new WPF_Profile_Page_Schema();
				break;

			default:
				$schema = new WPF_Web_Page_Schema();
		}

		// スキーマを出力
		if ( $schema ) {
			echo $schema->get_script(); // phpcs:ignore WordPress.Security.EscapeOutput
		}

		// パンくずリストスキーマを設定
		$breadcrumbs            = WPF_Template_Tags::get_the_breadcrumbs();
		$breadcrumb_list_schema = new WPF_Breadcrumb_List_Schema();
		$counter                = 1;
		if ( ! empty( $breadcrumbs ) ) {
			foreach ( $breadcrumbs as $breadcrumb ) {
				$breadcrumb_list_schema->add_item( $breadcrumb['text'], $breadcrumb['link'], $counter );
				$counter++;
			}

			// パンくずリストスキーマを出力
			if ( $breadcrumb_list_schema ) {
				echo $breadcrumb_list_schema->get_script(); // phpcs:ignore WordPress.Security.EscapeOutput
			}
		}
	}

	/**
	 * wp_get_archives のHTML属性を変更する。
	 *
	 * @link https://developer.wordpress.org/reference/hooks/get_archives_link/
	 *
	 * @since 0.1.0
	 * @param string $link_html アーカイブリストのHTMLコンテンツ。
	 * @param string $url アーカイブへのURL。
	 * @param string $text アーカイブテキスト。
	 * @return string アーカイブのHTMLリンク。
	 */
	public static function add_attributes_to_archives_link( $link_html, $url, $text ) {
		$link_html = preg_replace( '@<li>@i', '<li class="menu-item">', $link_html );
		$link_html = preg_replace( '@<a@i', '<a title="' . sprintf( /* translators: 年月日 */ _x( '%sのすべての投稿を見る', 'cta text to date archive', 'wordpressfoundation' ), $text ) . '"', $link_html );
		return $link_html;
	}

	/**
	 * 管理画面のコアアップデートを促す通知を非表示にする。
	 */
	public static function hide_core_update_notice() {
		if ( ! current_user_can( 'update_core' ) ) {
			remove_action( 'admin_notices', 'update_nag', 3 );
		}
	}

	/**
	 * body 要素に class 属性を追加する。
	 *
	 * 追加方法（例）: $classes[] = 'flow';
	 *
	 * @param string[] $classes ボディクラス名の配列。
	 * @return string[]
	 */
	public static function body_class( $classes ) {
		return $classes;
	}

	/**
	 * タームオーダーに基づいて`member`投稿のメニューオーダーを設定
	 *
	 * @param int     $post_id 投稿ID
	 * @param WP_Post $post 投稿オブジェクト
	 * @param bool    $update 既存の投稿が更新されているかどうか
	 * @return void
	 */
	public function save_post_update_member_menu_order_by_term_order( $post_id, $post, $update ) {
		// 自動保存の場合はスキップ
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		// リビジョンの場合はスキップ
		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		// 投稿タイプをチェック
		if ( ! in_array( $post->post_type, array( 'member' ), true ) ) {
			return;
		}

		// 投稿に紐づくタームのIDを取得
		$terms = wp_get_post_terms( $post_id, 'member_cat', array( 'fields' => 'ids' ) );

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return;
		}

		$orders = array();

		// 各タームとその祖先の_wpf_term_orderの合計を計算
		foreach ( $terms as $term_id ) {
			$term_name = get_term( $term_id, 'member_cat' );
			$order     = (int) get_term_meta( $term_id, '_wpf_term_order', true );
			$orders[]  = $order;
		}

		// 最小のorder値を取得
		$min_order = min( $orders );

		// メニューオーダーを更新
		// 無限ループを防ぐためにアクションを一時的に削除
		remove_action( 'save_post', array( $this, 'save_post_update_member_menu_order_by_term_order' ), 10, 3 );
		wp_update_post(
			array(
				'ID'         => $post_id,
				'menu_order' => $min_order,
			)
		);
		add_action( 'save_post', array( $this, 'save_post_update_member_menu_order_by_term_order' ), 10, 3 );
	}

	/**
	 * `member_cat` タクソノミータームを更新した時に `wpf_term_order` 値に基づいてメンバーの `menu_order' を更新
	 *
	 * @param int    $term_id タームID。
	 * @param int    $tt_id タームタクソノミーID。
	 * @param string $taxonomy タクソノミー名。
	 * @param bool   $update 既存のタームが更新されているかどうか。
	 * @param array  $args wp_insert_term() に渡される引数。
	 * @return void
	 */
	public function save_term_update_member_menu_order_by_term_order( $term_id, $tt_id, $taxonomy, $update, $args ) {
		if ( 'member_cat' === $taxonomy ) {
			global $wpdb;

			$query_args = array(
				'post_type'      => array( 'member' ),
				'posts_per_page' => -1,
				'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
					array(
						'taxonomy'         => $taxonomy,
						'field'            => 'term_id',
						'terms'            => $term_id,
						'include_children' => false,
					),
				),
			);
			$query      = new WP_Query( $query_args );
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();

					global $post;

					$terms  = get_the_terms( $post->ID, $taxonomy );
					$orders = array();
					foreach ( $terms as $term ) {
						$order        = 0;
						$ancestor_ids = get_ancestors( $term->term_id, $taxonomy );
						if ( ! empty( $ancestor_ids ) ) {
							foreach ( $ancestor_ids as $ancestor_id ) {
								$ancestor_term_order = (int) get_term_meta( $ancestor_id, '_wpf_term_order', true );
								$order              += $ancestor_term_order;
							}
						}
						$term_order = (int) get_term_meta( $term->term_id, '_wpf_term_order', true );
						$order     += $term_order;
						array_push( $orders, $order );
					}

					$min_order = min( $orders );

					$wpdb->update( $wpdb->posts, array( 'menu_order' => $min_order ), array( 'ID' => $post->ID ) ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
				}
				wp_reset_postdata();
			}
		}
	}
}
