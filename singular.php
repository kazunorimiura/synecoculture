<?php
/**
 * 個別投稿ページテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

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
			$wpf_show_toc = get_post_meta( get_the_ID(), '_wpf_show_toc', true );
			?>

			<div class="page-main">
				<?php
				if ( $wpf_show_toc ) {
					$wpf_toc      = new WPF_Toc();
					$wpf_content  = $wpf_toc->get_the_content();
					$wpf_toc_menu = $wpf_toc->get_html_menu( $wpf_content );

					if ( $wpf_toc_menu ) {
						?>
						<div class="singular__sidebar lg:hidden-yes">
							<div class="singular__sidebar__item">
								<div class="singular__sidebar__item__header">
									<div class="syneco-overline">
										<div class="syneco-overline__icon">
											<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<div class="syneco-overline__text">In this article</div>
									</div>
								</div>

								<nav class="singular__toc toc flow over-scroll" aria-label="In this article">
									<?php echo $wpf_toc_menu; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</nav>
							</div>
						</div>
						<?php
					}
				}
				?>

				<div class="singular__main">
					<?php
					if ( $wpf_show_toc && $wpf_toc_menu ) {
						?>
						<div class="singular__toc:fold hidden-yes lg:hidden-no">
							<details class="toc flow" aria-label="In this article">
								<summary>
									<div class="syneco-overline">
										<div class="syneco-overline__icon">
											<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<div class="syneco-overline__text">In this article</div>
									</div>
								</summary>

								<?php echo $wpf_toc_menu; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</details>
						</div>
						<?php
					}
					?>

					<div class="prose">
						<?php
						// コンテンツ
						if ( $wpf_show_toc ) {
							echo $wpf_content; // phpcs:ignore WordPress.Security.EscapeOutput
						} else {
							the_content();
						}
						?>

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
						$wpf_show_author        = is_single() && ! (bool) get_option( 'wpf_disable_author_page' ) && 'post' === get_post_type();
						$wpf_show_comments      = comments_open() || get_comments_number();
						$wpf_show_post_nav      = is_single() && ( $wpf_next_post || $wpf_prev_post );
						$wpf_show_related_posts = isset( $wpf_related_posts_query ) && $wpf_related_posts_query->have_posts();
						$wpf_show_back_link     = 'post' === get_post_type();

						if ( $wpf_show_link_pages ||
							$wpf_show_tags ||
							$wpf_show_author ||
							$wpf_show_comments ||
							$wpf_show_post_nav ||
							$wpf_show_related_posts ||
							$wpf_show_back_link
							) {
							?>
							<div class="flow" style="--flow-space: var(--space-s5)">
								<?php
								// ページ区切り
								if ( $wpf_show_link_pages ) {
									echo $wpf_link_pages; // phpcs:ignore WordPress.Security.EscapeOutput 
								}
								?>

								<?php
								// タグ
								if ( $wpf_show_tags && is_singular( 'blog' ) ) {
									?>
									<nav class="tags cluster widget font-text--xs" aria-label="<?php esc_attr_e( 'タグ', 'wordpressfoundation' ); ?>">
										<?php echo $wpf_terms; // phpcs:ignore WordPress.Security.EscapeOutput ?>
									</nav>
									<?php
								}
								?>

								<?php
								// 著者プロフィール
								if ( $wpf_show_author ) {
									$wpf_avatar           = get_avatar( get_the_author_meta( 'ID' ), 300 );
									$wpf_display_name     = function_exists( 'pll__' ) ? pll__( get_the_author_meta( 'display_name', get_the_author_meta( 'ID' ) ), 'wordpressfoundation' ) : get_the_author_meta( 'display_name', get_the_author_meta( 'ID' ) );
									$wpf_position         = function_exists( 'pll__' ) ? pll__( get_the_author_meta( 'position', get_the_author_meta( 'ID' ) ), 'wordpressfoundation' ) : get_the_author_meta( 'position', get_the_author_meta( 'ID' ) );
									$wpf_description      = get_the_author_meta( 'description', get_the_author_meta( 'ID' ) );
									$wpf_social_links     = array(
										'x'         => get_the_author_meta( 'x', get_the_author_meta( 'ID' ) ),
										'instagram' => get_the_author_meta( 'instagram', get_the_author_meta( 'ID' ) ),
										'facebook'  => get_the_author_meta( 'facebook', get_the_author_meta( 'ID' ) ),
									);
									$wpf_social_icon_size = 36;
									?>
									<div class="author-info widget">
										<?php
										// アバター
										if ( ! empty( $wpf_avatar ) ) {
											?>
											<a 
												href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>" 
												class="avatar" 
												title="<?php echo esc_attr( /* translators: 著者名 */ sprintf( __( '%sのプロフィールを見る', 'wordpressfoundation' ), $wpf_display_name ) ); ?>"
												aria-label="<?php echo esc_attr( /* translators: 著者名 */ sprintf( __( '%sのプロフィールを見る', 'wordpressfoundation' ), $wpf_display_name ) ); ?>">
												<?php echo $wpf_avatar; // phpcs:ignore WordPress.Security.EscapeOutput ?>
											</a>
											<?php
										}
										?>

										<div>
											<?php
											// 著者名
											if ( ! empty( $wpf_display_name ) ) {
												?>
												<a class="link-muted" href="<?php echo esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ); ?>">
													<?php echo esc_html( $wpf_display_name ); ?>
												</a>
												<?php
											}

											// 著者の肩書き
											if ( ! empty( $wpf_position ) ) {
												?>
												<p style="font: var(--font-text--sm)">
													<?php echo esc_html( $wpf_position ); ?>
												</p>
												<?php
											}
											?>
										</div>
									</div>
									<?php
								}

								// コメント
								if ( $wpf_show_comments && is_singular( 'blog' ) ) {
									comments_template();
								}

								// 投稿ナビゲーション
								if ( $wpf_show_post_nav && is_singular( 'post' ) || is_singular( 'blog' ) ) {
									?>
									<nav class="navigation post-navigation widget" aria-label="<?php esc_attr_e( '投稿ナビゲーション', 'wordpressfoundation' ); ?>">
										<div class="nav-links switcher">
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

								// 関連記事
								if ( $wpf_show_related_posts ) {
									?>
									<div class="widget">
										<div class="flow" style="--flow-space: var(--space-s0em)">
											<div class="widget-header switcher">
												<h2 class="widget-header__title">
													<?php esc_html_e( '関連記事', 'wordpressfoundation' ); ?>
												</h2>

												<a class="widget-header__cta link-muted" href="<?php echo esc_url( get_post_type_archive_link( $wpf_post_type ) ); ?>">
													<small class="with-icon">
														<span><?php esc_html_e( '一覧へ', 'wordpressfoundation' ); ?></span>
														<?php echo WPF_Icons::get_svg( 'ui', 'arrow_right', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
													</small>
												</a>
											</div>

											<div class="grid">
											<?php
											while ( $wpf_related_posts_query->have_posts() ) {
												$wpf_related_posts_query->the_post();
												?>
												<div class="switcher fd-row-reverse ai-flex-start" style="--switcher-threshold: 1px; --switcher-gap: var(--space-s-1)">
													<div class="flow" style="--switcher-grow: 2; --flow-space: 0.25em; font: var(--font-text--sm)">
														<a class="link-muted" href="<?php the_permalink(); ?>">
															<?php the_title(); ?>
														</a>

														<p style="font: var(--font-text--xs); color: var(--color-content-tertiary)">
															<?php echo $wpf_template_tags::get_the_publish_date_tag(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
														</p>
													</div>

													<a 
														class="frame radius:lg bg-color-background-secondary" 
														style="--n: 1; --d: 1"
														href="<?php the_permalink(); ?>" 
														aria-hidden="true" 
														tabindex="-1">
														<?php $wpf_template_tags::the_image( get_post_thumbnail_id() ); ?>
													</a>
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

								// 戻るリンク
								if ( $wpf_show_back_link ) {
									$wpf_back_link = $wpf_template_tags::get_the_back_link();
									if ( $wpf_back_link ) {
										?>
										<div class="widget">
											<?php echo $wpf_back_link; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
										</div>
										<?php
									}
								}
								?>
							</div>
							<?php
						}
						?>
					</div>
				</div>
			</div>
		</main>
		<?php
	}
}

get_footer();
