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

// Polylangフレンドリーなカスタム投稿タイプパーマリンク。
new WPF_CPT_Rewrite();
new WPF_CPT_Permalink();
new WPF_CPT_Filters();

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

/**
 * 旧ブログ（lionテーマ使用）からメディアをインポートする際、旧ブログのテーマと同様の画像サイズバリエーションで生成されるようにする。
 * ※この設定はマイグレーション用であるため、マイグレーション後は必ずコメントアウトすること。
 *
 * @return void
 */
function wpf_old_theme_image_sizes() {
	// サムネイルサイズを変更
	update_option( 'thumbnail_size_w', 160 );
	update_option( 'thumbnail_size_h', 160 );

	// 中サイズを変更
	update_option( 'medium_size_w', 600 );
	update_option( 'medium_size_h', 600 );
}
// add_action( 'after_setup_theme', 'wpf_old_theme_image_sizes' );
