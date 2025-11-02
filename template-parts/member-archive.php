<?php
/**
 * `member`投稿タイプのアーカイブテンプレート
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

	<div class="page-main wrapper:stretch">
		<div class="archive-member">
			<div class="archive-member__main">
				<?php
				if ( have_posts() ) {
					?>
					<div class="archive-member__items">
						<?php
						while ( have_posts() ) {
							the_post();
							?>
							<article class="archive-member__item">
								<div class="archive-member__item__main">
									<?php
									/**
									 * タイトル
									 */
									$wpf_title = get_the_title();
									if ( $wpf_title ) {
										?>
										<h2 class="archive-member__item__title">
											<a 
												href="<?php the_permalink(); ?>"
												class="link-muted">
												<?php echo $wpf_title; // phpcs:ignore WordPress.Security.EscapeOutput ?>
											</a>
										</h2>
										<?php
									}
									?>

									<?php
									/**
									 * 肩書き（ターム）
									 */
									$wpf_terms = WPF_Utils::get_the_terms();
									if ( ! empty( $wpf_terms ) && 'uncategorized' !== $wpf_terms[0]->slug ) {
										?>
										<div class="archive-member__item__position">
											<?php echo esc_html( $wpf_terms[0]->name ); ?>
										</div>
										<?php
									}
									?>
								</div>

								<a href="<?php the_permalink(); ?>" class="archive-member__item__thubmnail frame" aria-hidden="true" tabindex="-1">
									<?php $wpf_template_tags::the_member_image( get_post_thumbnail_id(), 'member-thumbnail' ); ?>
								</a>
							</article>
							<?php
						}
						?>
					</div>

					<?php
					the_posts_pagination(
						array(
							'prev_text'          => sprintf(
								'%s <span class="screen-reader-text">%s</span>',
								WPF_Icons::get_svg( 'ui', 'arrow_left' ),
								esc_html__( '前へ', 'wordpressfoundation' )
							),
							'next_text'          => sprintf(
								'<span class="screen-reader-text">%s</span> %s',
								esc_html__( '次へ', 'wordpressfoundation' ),
								WPF_Icons::get_svg( 'ui', 'arrow_right' )
							),
							'before_page_number' => '<span class="screen-reader-text">' . __( '投稿', 'wordpressfoundation' ) . ' </span>',
						)
					);
					?>

					<?php
				} else {
					?>
					<div class="prose">
						<p><?php esc_html_e( '投稿が見つかりませんでした。', 'wordpressfoundation' ); ?></p>
					</div>
					<?php
				}
				?>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
