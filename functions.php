<?php
/**
 * テーマの関数とフィルタリング
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/*
 * テーマ
 */

require_once get_template_directory() . '/vendor/autoload.php';

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// 機能強化
new WPF_Template_Functions();

// ショートコード
new WPF_Shortcode();

// カスタマイザー
new WPF_Customizer();

// テンプレートタグ
$wpf_template_tags = new WPF_Template_Tags();

/*
 * 拡張
 */

// タクソノミーに順序フィールドを追加
new WPF_Term_Order();

// Polylangフレンドリーなカスタム投稿タイプパーマリンク。
new WPF_CPT_Rewrite();
new WPF_CPT_Permalink();
new WPF_CPT_Filters();

// カスタムREST APIルート
new WPF_Rest_API();

// 目次
$wpf_toc = new WPF_Toc();

// WebP生成オプション
$wpf_webp_handler = new WPF_Selective_WebP_Image_Handler();

// WebP配信
new WPF_Auto_WebP_Delivery();

/**
 * ページ単位WebP生成機能の初期化
 *
 * @return void
 */
function wpf_init_page_webp_scanner() {
	if ( is_admin() ) {
		global $wpf_webp_handler;
		new WPF_Page_WebP_Scanner( $wpf_webp_handler );
	}
}
add_action( 'init', 'wpf_init_page_webp_scanner' );

/*
 * 統合
 */

// Polylangプラグインの機能拡張
if ( is_plugin_active( 'polylang-pro/polylang.php' ) || is_plugin_active( 'polylang/polylang.php' ) ) {
	$wpf_polylang_functions = new WPF_Polylang_Functions();
}

// MW WP Formプラグインの機能拡張
if ( is_plugin_active( 'mw-wp-form/mw-wp-form.php' ) ) {
	require_once get_template_directory() . '/includes/integrations/mw-wp-form/functions.php';
}

// Smart Custom Fieldsプラグインの機能拡張
if ( is_plugin_active( 'smart-custom-fields/smart-custom-fields.php' ) ) {
	new WPF_Smart_Cf_Register_Fields();
}

// 主要なSEOプラグインが有効化されていない場合、wp-sitemap.xmlの最適化を必要最低限行う
if ( ! is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' ) && ! is_plugin_active( 'wordpress-seo/wp-seo.php' ) ) {
	// wp-sitemap.xmlからユーザーを除外
	add_filter(
		'wp_sitemaps_add_provider',
		function( $provider, $name ) {
			if ( 'users' === $name ) {
				return false;
			}

			return $provider;
		},
		10,
		2
	);

	// wp-sitemap.xmlから特定のtaxアーカイブを除外
	add_filter(
		'wp_sitemaps_taxonomies',
		function( $taxonomies ) {
			unset( $taxonomies['post_tag'] );
			unset( $taxonomies['blog_tag'] );
			unset( $taxonomies['project_tag'] );
			unset( $taxonomies['member_cat'] );
			unset( $taxonomies['member_tag'] );
			unset( $taxonomies['case_study_tag'] );
			unset( $taxonomies['glossary_tag'] );
			unset( $taxonomies['career_tag'] );
			return $taxonomies;
		}
	);

	// wp-sitemap.xmlから未分類カテゴリーを除外
	add_filter(
		'wp_sitemaps_taxonomies_query_args',
		function( $args, $taxonomy ) {
			if ( 'category' === $taxonomy ) {
				$uncategorized = get_term_by( 'slug', 'uncategorized', 'category' );
				if ( $uncategorized ) {
					$args['exclude'] = array( $uncategorized->term_id );
				}

				$uncategorized = get_term_by( 'slug', 'uncategorized', 'blog_cat' );
				if ( $uncategorized ) {
					$args['exclude'] = array( $uncategorized->term_id );
				}
			}
			return $args;
		},
		10,
		2
	);

	// 特定の投稿タイプをサイトマップから除外する
	// シングルページを恒久リダイレクトしている投稿タイプは除外するべき
	add_filter(
		'wp_sitemaps_post_types',
		function( $post_types ) {
            // phpcs:ignore
            // if ( isset( $post_types['testimonial'] ) ) {
			// unset( $post_types['testimonial'] );
			// }
			return $post_types;
		}
	);
}
