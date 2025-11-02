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

/**
 * WordPress 6.4以降で自動的に追加される画像最適化機能を無効化（旧コンテンツにおいて画像がオーバーフローするケースがあるため）
 * https://make.wordpress.org/core/2024/10/18/auto-sizes-for-lazy-loaded-images-in-wordpress-6-7/
 */
add_filter( 'wp_img_tag_add_auto_sizes', '__return_false' );

/**
 * アタッチメントのsizes属性を設定
 *
 * @param string[]     $attr 属性名をキーにしたイメージマークアップの属性値の配列
 * @param WP_Post      $attachment 画像アタッチメントの投稿
 * @param string|int[] $size 要求された画像サイズ。登録されている任意の画像サイズ名、またはピクセル単位の幅と高さの値の配列（この順番で）を指定できる
 * @return $attr
 * @since 0.1.0
 */
function wpf_child_attachment_image_attributes( $attr, $attachment, $size ) {
	if ( in_array( $size, array( 'medium', 'blog-thumbnail', 'case-study-thumbnail' ), true ) ) {
		$attr['sizes'] = '(max-width: 768px) 82.68vw, (max-width: 1200px) 38.33vw, 30.15vw';
	}

	if ( in_array( $size, array( 'large' ), true ) ) {
		$attr['sizes'] = '(max-width: 720px) 89.44vw, 720px';
	}

	if ( in_array( $size, array( 'member-thumbnail' ), true ) ) {
		$attr['sizes'] = '(max-width: 768px) 43.35vw, (max-width: 1200px) 19.9vw, 22.28vw';
	}

	if ( in_array( $size, array( 'news-thumbnail' ), true ) ) {
		$attr['sizes'] = '(max-width: 600px) 23.57vw, (max-width: 768px) 89.71vw, (max-width: 1200px) 43.66vw, 34.95vw';
	}

	if ( in_array( $size, array( 'stretch' ), true ) ) {
		$attr['sizes'] = '97.22vw';
	}

	if ( in_array( $size, array( 'page-header-thumbnail' ), true ) ) {
		$attr['sizes'] = '(max-width: 600px) 89vw, 14.4vw';
	}

	if ( in_array( $size, array( 'main-visual' ), true ) ) {
		$attr['sizes'] = '(max-width: 750px) 89.6vw, 45.71vw';
	}

	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'wpf_child_attachment_image_attributes', 10, 3 );
