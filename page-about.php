<?php
/**
 * `about`固定ページのテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals,WordPress.WP.GlobalVariablesOverride

get_header();

global $wpf_template_tags;
?>

<?php
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		?>
		<main id="content">
			<?php
			get_template_part(
				'template-parts/page',
				'header',
				array(
					'subtitle' => $wpf_template_tags::get_the_page_subtitle(),
				)
			);
			?>

			<?php
			// イントロダクション
			$wpf_about_intro = do_shortcode( '[wpf_about_intro]' );
			if ( ! empty( $wpf_about_intro ) ) {
				echo $wpf_about_intro; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>

			<?php
			$query = new WP_Query(
				array(
					'post_type'      => 'page',
					'name'           => 'home',
					'posts_per_page' => 1,
				)
			);

			if ( $query->have_posts() ) {
				?>
				<div id="our-purpose">
					<?php
					while ( $query->have_posts() ) {
						$query->the_post();

						// Our Purpose
						$wpf_our_purpose_for_about = do_shortcode( '[wpf_our_purpose_for_about]' );
						if ( ! empty( $wpf_our_purpose_for_about ) ) {
							echo $wpf_our_purpose_for_about; // phpcs:ignore WordPress.Security.EscapeOutput
						}
					}
					wp_reset_postdata();
					?>
				</div>
				<?php
			}
			?>

			<?php
			// Our Values
			$wpf_our_values = do_shortcode( '[wpf_our_values]' );
			if ( ! empty( $wpf_our_values ) ) {
				echo $wpf_our_values; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>

			<?php
			// 子ページリンクリスト
			$wpf_about_child_page_links = do_shortcode( '[wpf_about_child_page_links]' );
			if ( ! empty( $wpf_about_child_page_links ) ) {
				echo $wpf_about_child_page_links; // phpcs:ignore WordPress.Security.EscapeOutput
			}
			?>
		</main>
		<?php
	}
}

get_footer();
