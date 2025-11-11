<?php
/**
 * `manual`投稿タイプの個別投稿ページテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

get_header();

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals,WordPress.WP.GlobalVariablesOverride

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
			$wpf_show_toc = get_post_meta( get_the_ID(), '_wpf_show_toc', true );
			$wpf_toc      = new WPF_Toc();
			$wpf_content  = $wpf_toc->get_the_content( apply_filters( 'the_content', get_the_content() ) );
			$wpf_toc_menu = $wpf_toc->get_html_menu( $wpf_content );
			?>

			<div class="manual-main">
				<div class="manual-main__content">
					<?php
					$current_post_id = get_the_ID();

					$args = array(
						'post_type'      => 'manual',
						'posts_per_page' => -1,
						'orderby'        => 'date',
						'order'          => 'DESC',
						'post_status'    => 'publish',
					);

					$manual_posts = new WP_Query( $args );

					if ( $manual_posts->have_posts() ) :
						?>
						<div class="manual-nav-container">
							<nav class="manual-nav">
								<div class="manual-nav__header">
									<div class="syneco-overline">
										<div class="syneco-overline__icon">
											<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<div class="syneco-overline__text">Chapters</div>
									</div>

									<button class="manual-nav__toggle" aria-expanded="false" data-acc-target="manual-list" data-open-text="<?php echo esc_html_e( 'チャプターを開く', 'wordpressfoundation' ); ?>" data-close-text="<?php echo esc_html_e( 'チャプターを閉じる', 'wordpressfoundation' ); ?>">
										<span class="screen-reader-text"><?php echo esc_html_e( 'チャプターを開く', 'wordpressfoundation' ); ?></span>
									</button>
								</div>

								<ul  id="manual-list" class="manual-list">
									<?php
									while ( $manual_posts->have_posts() ) :
										$manual_posts->the_post();
										?>
										<li class="<?php echo ( get_the_ID() === $current_post_id ) ? 'current-manual' : ''; ?>">
											<a href="<?php the_permalink(); ?>">
												<?php the_title(); ?>
											</a>
										</li>
									<?php endwhile; ?>
								</ul>
							</nav>
						</div>
						<?php wp_reset_postdata(); ?>
					<?php endif; ?>

					<div class="manual-main__content__body">
						<div class="prose">
							<h1 class="manual-title">
								<?php
								// タイトル
								the_title();
								?>
							</h1>

							<?php
							if ( $wpf_show_toc && $wpf_toc_menu ) {
								?>
								<div class="manual-main__content__sidebar">
									<div class="manual-main__content__sidebar__header">
										<div class="syneco-overline">
											<div class="syneco-overline__icon">
												<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
											</div>
											<div class="syneco-overline__text">In this article</div>
										</div>

										<button class="toc__toggle" aria-expanded="false" data-acc-target="manual-main__content__sidebar__toc" data-open-text="<?php echo esc_html_e( '目次を開く', 'wordpressfoundation' ); ?>" data-close-text="<?php echo esc_html_e( '目次を閉じる', 'wordpressfoundation' ); ?>">
											<span class="screen-reader-text"><?php echo esc_html_e( '目次を開く', 'wordpressfoundation' ); ?></span>
										</button>
									</div>

									<nav id="manual-main__content__sidebar__toc" class="manual-main__content__sidebar__toc toc flow over-scroll" aria-label="In this article">
										<?php echo $wpf_toc_menu; // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</nav>
								</div>
								<?php
							}
							?>

							<?php
							// コンテンツ
							if ( $wpf_show_toc ) {
								echo $wpf_content; // phpcs:ignore WordPress.Security.EscapeOutput
							} else {
								the_content();
							}
							?>
						</div>

						<?php
						// ページ区切り
						$wpf_link_pages = wp_link_pages(
							array(
								'before'           => '<nav class="navigation pagination" aria-label="' . esc_attr__( 'ページナビゲーション', 'wordpressfoundation' ) . '"><span class="screen-reader-text">' . __( 'ページナビゲーション', 'wordpressfoundation' ) . ' </span><div class="nav-links">',
								'after'            => '</div></nav>',
								'next_or_number'   => 'next_and_number',
								'previouspagelink' => sprintf(
									'%s <span>%s</span>',
									WPF_Icons::get_svg( 'ui', 'arrow_left' ),
									esc_html__( '前へ', 'wordpressfoundation' )
								),
								'nextpagelink'     => sprintf(
									'<span>%s</span> %s',
									esc_html__( '次へ', 'wordpressfoundation' ),
									WPF_Icons::get_svg( 'ui', 'arrow_right' )
								),
								'pagelink'         => esc_html__( '%', 'wordpressfoundation' ),
								'echo'             => false,
							)
						);

						// タグ
						$wpf_terms = $wpf_template_tags::get_the_term_links( $post->ID );

						// 投稿ナビゲーション
						$wpf_next_post = get_next_post();
						$wpf_prev_post = get_previous_post();

						// 関連記事
						$wpf_post_type           = get_post_type( $post->ID );
						$wpf_related_posts_query = $wpf_template_tags::get_the_related_posts_query();

						$wpf_show_link_pages    = ! empty( $wpf_link_pages );
						$wpf_show_tags          = is_single() && $wpf_terms;
						$wpf_show_comments      = comments_open() || get_comments_number();
						$wpf_show_post_nav      = is_single() && ( $wpf_next_post || $wpf_prev_post );
						$wpf_show_related_posts = isset( $wpf_related_posts_query ) && $wpf_related_posts_query->have_posts() && in_array( get_post_type(), array( 'post', 'blog' ), true );
						$wpf_show_back_link     = in_array( get_post_type(), array( 'post', 'blog' ), true );

						if ( $wpf_show_link_pages ||
							$wpf_show_tags ||
							$wpf_show_comments ||
							$wpf_show_post_nav ||
							$wpf_show_related_posts ||
							$wpf_show_back_link
							) {
							?>
							<div class="flow" style="--flow-space: var(--space-s5)">
								<?php
								// 投稿ナビゲーション
								if ( $wpf_show_post_nav ) {
									?>
									<nav class="navigation post-navigation widget" aria-label="<?php esc_attr_e( '投稿ナビゲーション', 'wordpressfoundation' ); ?>">
										<div class="nav-links">
										<?php
										if ( $wpf_next_post ) {
											?>
											<a class="nav-previous link-muted" href="<?php echo esc_url( get_permalink( $wpf_next_post->ID ) ); ?>" rel="prev">
												<div class="eyebrow with-icon">
													<?php echo WPF_Icons::get_svg( 'ui', 'arrow_left' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
													<span><?php esc_html_e( '前へ', 'wordpressfoundation' ); ?></span>
												</div>

												<div>
													<?php echo esc_html( get_the_title( $wpf_next_post->ID ) ); ?>
												</div>
											</a>
											<?php
										}

										if ( $wpf_prev_post ) {
											?>
											<a class="nav-next link-muted" href="<?php echo esc_url( get_permalink( $wpf_prev_post->ID ) ); ?>" rel="next">
												<div class="eyebrow with-icon">
													<span><?php esc_html_e( '次へ', 'wordpressfoundation' ); ?></span>
													<?php echo WPF_Icons::get_svg( 'ui', 'arrow_right' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
												</div>

												<div>
													<?php echo esc_html( get_the_title( $wpf_prev_post->ID ) ); ?>
												</div>
											</a>
											<?php
										}
										?>
										</div>
									</nav>
									<?php
								}
								?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>

			<div class="manual-footer">
				<?php
				$wpf_page_for_posts = WPF_Utils::get_page_for_posts();
				if ( $wpf_page_for_posts ) {
					$wpf_copyright = SCF::get( '_wpf_manual__copyright__footer', $wpf_page_for_posts );
					if ( ! empty( $wpf_copyright ) ) {
						echo $wpf_copyright; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					}
					?>
					<?php
				}
				?>
			</div>
		</main>
		<?php
	}
}

get_footer();
