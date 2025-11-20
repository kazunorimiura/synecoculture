<?php
/**
 * `career`投稿タイプのアーカイブテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals,WordPress.WP.GlobalVariablesOverride

get_header();

global $wpf_template_tags;
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

	<div class="page-main">
		<?php
		if ( have_posts() ) {
			?>
			<div class="wrapper:wide flow">
				<h2>
					<?php esc_html_e( '募集中の職種一覧', 'wordpressfoundation' ); ?>
				</h2>

				<div class="archive-career__items">
					<?php
					while ( have_posts() ) {
						the_post();
						?>
						<article class="archive-glossary__item">
							<?php
							$image_url = '';
							$image     = wp_get_attachment_image_src( get_post_thumbnail_id(), 'rich_link' );
							if ( $image ) {
								$image_url = $image[0];
							}
							$rich_link = do_shortcode( '[wpf_rich_link url="' . get_the_permalink() . '" text="' . get_the_title() . '" image_url="' . $image_url . '" no_image="false"]' );
							if ( ! empty( $rich_link ) ) {
								echo $rich_link; // phpcs:ignore WordPress.Security.EscapeOutput
							}
							?>
						</article>
						<?php
					}
					?>
				</div>
			</div>
			<?php
		} else {
			?>
			<div class="prose">
				<p><?php esc_html_e( '現在募集中の職種はありません。', 'wordpressfoundation' ); ?></p>
			</div>
			<?php
		}
		?>
	</div>
</main>

<?php
get_footer();
