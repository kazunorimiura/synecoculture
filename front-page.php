<?php
/**
 * フロントページテンプレート。
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

get_header();

global $wpf_template_tags;
?>

<main id="content">
	<?php
	// メインビジュアル
	$wpf_syneco_branding = do_shortcode( '[wpf_syneco_branding]' );
	if ( ! empty( $wpf_syneco_branding ) ) {
		echo $wpf_template_tags::kses_post( $wpf_syneco_branding ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	// Our Story
	$wpf_our_story = do_shortcode( '[wpf_our_story]' );
	if ( ! empty( $wpf_our_story ) ) {
		echo $wpf_template_tags::kses_post( $wpf_our_story ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	// Our Approach
	$wpf_our_approach = do_shortcode( '[wpf_our_approach]' );
	if ( ! empty( $wpf_our_approach ) ) {
		echo $wpf_template_tags::kses_post( $wpf_our_approach ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	// Our Purpose
	$wpf_our_purpose = do_shortcode( '[wpf_our_purpose]' );
	if ( ! empty( $wpf_our_purpose ) ) {
		echo $wpf_template_tags::kses_post( $wpf_our_purpose ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	// Featured Projects
	$wpf_featured_projects = do_shortcode( '[wpf_featured_projects]' );
	if ( ! empty( $wpf_featured_projects ) ) {
		echo $wpf_template_tags::kses_post( $wpf_featured_projects ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	// シネコカルチャーの世界へようこそ
	$wpf_dive_into_synecoculture = do_shortcode( '[wpf_dive_into_synecoculture]' );
	if ( ! empty( $wpf_dive_into_synecoculture ) ) {
		echo $wpf_template_tags::kses_post( $wpf_dive_into_synecoculture ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	// ブログバナー
	$wpf_blog_banner = do_shortcode( '[wpf_blog_banner]' );
	if ( ! empty( $wpf_blog_banner ) ) {
		echo $wpf_template_tags::kses_post( $wpf_blog_banner ); // phpcs:ignore WordPress.Security.EscapeOutput
	}

	// ニューススライダー
	$wpf_news_slider = do_shortcode( '[wpf_news_slider]' );
	if ( ! empty( $wpf_news_slider ) ) {
		echo $wpf_template_tags::kses_post( $wpf_news_slider ); // phpcs:ignore WordPress.Security.EscapeOutput
	}
	?>
</main>

<?php
get_footer();
