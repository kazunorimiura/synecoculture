<?php
/**
 * インデックステンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

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
		<div class="flow">
			<?php
			if ( is_404() ) {
				?>
				<div class="prose">
					<p><?php esc_html_e( '以下の可能性があります。', 'wordpressfoundation' ); ?></p>
					<ul>
						<li>
							<?php esc_html_e( 'ご覧になっていたページからのリンクが無効になっている', 'wordpressfoundation' ); ?>
						</li>
						<li>
							<?php esc_html_e( 'アドレス（URL）のタイプミスがある', 'wordpressfoundation' ); ?>
						</li>
						<li>
							<?php esc_html_e( '当該ページの公開が終了している', 'wordpressfoundation' ); ?>
						</li>
					</ul>
					<p><?php esc_html_e( '上部（または下部）のメニューから目的のページをお探しいただくか、検索をお試しください。', 'wordpressfoundation' ); ?></p>

					<div style="--flow-space: var(--space-s3)">
						<?php get_search_form(); ?>
					</div>
				</div>
				<?php
			} else {
				if ( ! is_author() && ! is_search() ) {
					if ( is_date() ) {
						$wpf_date_navigation = $wpf_template_tags::get_date_navigation();

						if ( $wpf_date_navigation ) {
							?>
							<div>
								<?php echo $wpf_date_navigation; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
							<?php
						}
					} else {
						$wpf_args = array();

						// uncategorizedタームを除外
						$wpf_taxonomies = WPF_Utils::get_object_public_taxonomies( WPF_Utils::get_post_type() );
						if ( ! empty( $wpf_taxonomies ) ) {
							$wpf_uncategorized_term = get_term_by( 'slug', 'uncategorized', $wpf_taxonomies[0] );

							if ( $wpf_uncategorized_term ) {
								$wpf_args['exclude'] = $wpf_uncategorized_term->term_id;
							}
						}

						$wpf_taxonomy_navigation = $wpf_template_tags::get_term_navigation( $wpf_args );

						if ( $wpf_taxonomy_navigation ) {
							?>
							<div>
								<?php echo $wpf_taxonomy_navigation; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</div>
							<?php
						}
					}
				}

				if ( have_posts() ) {
					?>
					<div class="flex-grid:3-2-1" style="--flow-space: var(--space-s5)">
						<?php
						while ( have_posts() ) {
							the_post();
							?>
							<article class="d-flex fd-column-reverse jc-flex-end gap-s0">
								<div class="flow" style="--flow-space: var(--space-s-1)">
									<?php
									$wpf_terms = WPF_Utils::get_the_terms();
									if ( ! empty( $wpf_terms ) && 'uncategorized' !== $wpf_terms[0]->slug ) {
										?>
										<p 
											style="--flow-space: var(--space-s-5)">
											<span class="pill font-text--xs">
												<?php echo esc_html( $wpf_terms[0]->name ); ?>
											</span>
										</p>
										<?php
									}

									$wpf_title = get_the_title();
									if ( $wpf_title ) {
										?>
										<p
											class="font-headline-2"
											style="--flow-space: var(--space-s-5)">
											<a 
												href="<?php the_permalink(); ?>"
												class="link-muted">
												<?php echo $wpf_title; // phpcs:ignore WordPress.Security.EscapeOutput ?>
											</a>
										</p>
										<?php
									}

									$wpf_excerpt = get_the_excerpt();
									if ( $wpf_excerpt ) {
										?>
										<p
											class="font-text--sm sm:hidden-yes"
											style="--flow-space: var(--space-s-5)">
											<?php echo $wpf_excerpt; // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</p>
										<?php
									}

									if ( ! is_search() ) {
										?>
										<p
											class="font-text--sm c-content-tertiary"
											style="--flow-space: var(--space-s-5)">
											<?php echo $wpf_template_tags::get_the_publish_date_tag(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</p>
										<?php
									}
									?>
								</div>

								<a 
									href="<?php the_permalink(); ?>" 
									class="frame radius:lg bg-color-background-secondary" 
									aria-hidden="true" 
									tabindex="-1">
									<?php $wpf_template_tags::the_image( get_post_thumbnail_id() ); ?>
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
				} else {
					?>
					<div class="prose">
						<p><?php esc_html_e( '投稿が見つかりませんでした。', 'wordpressfoundation' ); ?></p>
					</div>
					<?php
				}
			}
			?>
		</div>
	</div>
</main>

<?php
get_footer();
