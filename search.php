<?php
/**
 * 検索結果テンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

get_header();

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals,WordPress.WP.GlobalVariablesOverride

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

	<div class="page-main wrapper">
		<?php
		if ( have_posts() ) {
			?>
			<div class="archive-search">
				<?php
				while ( have_posts() ) {
					the_post();

					$excerpt = get_the_excerpt();
					?>
					<article class="archive-search__item">
						<div class="archive-search__item__main">
							<div class="archive-search__item__content">
								<?php
								$post_type        = get_post_type();
								$post_type_object = get_post_type_object( $post_type );

								$pt_name = '';

								if ( $post_type_object ) {
									$pt_slug = $post_type;

									if ( isset( $post_type_object->rewrite ) && isset( $post_type_object->rewrite['slug'] ) ) {
										$pt_slug = $post_type_object->rewrite['slug'];
									}

									$pt_page = get_page_by_path( $pt_slug );

									if ( $pt_page ) {
										$pt_page_id = $pt_page->ID;

										if ( function_exists( 'pll_get_post' ) ) {
											$pt_page_id = pll_get_post( $pt_page_id );

											if ( $pt_page->ID !== $pt_page_id ) {
												$pt_page = get_post( $pt_page_id );
											}
										}
										$pt_name = get_the_title( $pt_page->ID );
									}
								}

								if ( ! empty( $pt_name ) ) {
									?>
									<div class="archive-search__item__cat">
										<span><?php echo esc_html( $pt_name ); ?></span>
									</div>
									<?php
								}

								$title = get_the_title();
								if ( $title ) {
									?>
									<p class="archive-search__item__title">
										<a 
											href="<?php the_permalink(); ?>"
											class="link-muted">
											<?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</a>
									</p>
									<?php
								}

								if ( $excerpt ) {
									?>
									<p class="archive-search__item__excerpt">
										<?php echo $excerpt; // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</p>
									<?php
								}
								?>
							</div>

							<?php
							$thumbnail = $wpf_template_tags::get_the_image( get_post_thumbnail_id(), 'post_list', false, array(), false );
							if ( $thumbnail ) {
								?>
								<a 
									href="<?php the_permalink(); ?>" 
									class="archive-search__item__thumbnail frame" 
									aria-hidden="true" 
									tabindex="-1">
									<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</a>
								<?php
							}
							?>
						</div>

						<?php
						if ( $excerpt ) {
							?>
							<p class="archive-search__item__excerpt--mobile">
								<?php echo $excerpt; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</p>
							<?php
						}
						?>
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
		?>
	</div>
</main>

<?php
get_footer();
