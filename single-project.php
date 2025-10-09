<?php
/**
 * `project`投稿タイプの個別投稿ページテンプレート
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

			<div class="page-main wrapper:wide">
				<div class="single-project">
					<?php
					/**
					 * サイドバー
					 */
					$related_members = get_post_meta( get_the_ID(), '_wpf_related_members', true );
					if ( ! empty( $related_members ) ) {
						$query = new WP_Query(
							array(
								'post_type'      => 'member',
								'posts_per_page' => -1,
								'post__in'       => $related_members,
								'orderby'        => array(
									'title'      => 'ASC',
									'menu_order' => 'ASC',
								),
							)
						);
						if ( $query->have_posts() ) {
							?>
							<div class="single-project__sidebar">
								<div class="related-members">
									<div class="related-members__header">
										<div class="syneco-overline">
											<div class="syneco-overline__icon">
												<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
											</div>
											<div class="syneco-overline__text">Related Members</div>
										</div>
									</div>

									<div class="related-members__main">
										<?php
										while ( $query->have_posts() ) {
											$query->the_post();
											?>
											<div class="related-members__item">
												<div class="related-members__item__title">
													<?php the_title(); ?>
												</div>
											</div>
											<?php
										}
										wp_reset_postdata();
										?>
									</div>
								</div>
							</div>
							<?php
						}
					}
					?>

					<div class="single-project__main">
						<div class="prose">
							<?php
							/**
							 * コンテンツ
							 */
							the_content();
							?>
						</div>
					</div>
				</div>
			</div>
		</main>
		<?php
	}
}

get_footer();
