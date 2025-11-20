<?php
/**
 * `blog`投稿タイプのアーカイブテンプレート
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
		<div class="archive-blog">
			<?php
			/**
			 * サイドバー
			 */

			// カテゴリー
			$top_level_blog_cat_terms = get_terms(
				array(
					'taxonomy' => 'blog_cat',
					'parent'   => 0,  // 親がないタームのみ取得
				)
			);

			// 日付アーカイブ
			$date_navigation = $wpf_template_tags::get_date_navigation();

			if ( $top_level_blog_cat_terms && ! is_wp_error( $top_level_blog_cat_terms ) || ! empty( $date_navigation ) ) {
				$current_term = WPF_Template_Tags::get_the_current_term();
				?>
				<div class="archive-blog__sidebar">
					<?php
					if ( $top_level_blog_cat_terms && ! is_wp_error( $top_level_blog_cat_terms ) ) {
						$is_open = is_date() ? '' : ' open';
						?>
						<div class="archive-blog__sidebar-item">
							<details class="category-accordion" aria-label="<?php esc_attr_e( 'カテゴリー', 'wordpressfoundation' ); ?>"<?php echo esc_attr( $is_open ); ?>>
								<summary class="category-accordion__heading">
									<?php echo esc_html_e( 'カテゴリー', 'wordpressfoundation' ); ?>
								</summary>

								<nav class="category-accordion__main" aria-label="<?php echo esc_attr_e( 'カテゴリー', 'wordpressfoundation' ); ?>">
									<ul>
										<?php
										foreach ( $top_level_blog_cat_terms as $blog_cat_term ) {
											$aria_current_attr  = $current_term && $current_term->term_id === $blog_cat_term->term_id ? ' aria-current="page"' : '';
											$blog_cat_term_link = get_term_link( $blog_cat_term );
											if ( ! is_wp_error( $blog_cat_term_link ) ) {
												?>
												<li>
													<a href="<?php echo esc_url( $blog_cat_term_link ); ?>" class="category-accordion__item"<?php echo $aria_current_attr; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>>
														<?php echo esc_html( $blog_cat_term->name ); ?>
													</a>
												</li>
												<?php
											}
										}
										?>
									</ul>
								</nav>
							</details>
						</div>
						<?php
					}
					?>

					<?php
					if ( ! empty( $date_navigation ) ) {
						$is_open = is_date() ? ' open' : '';
						?>
						<div class="archive-blog__sidebar-item">
							<details class="category-accordion" aria-label="<?php esc_attr_e( 'アーカイブ', 'wordpressfoundation' ); ?>"<?php echo esc_attr( $is_open ); ?>>
								<summary class="category-accordion__heading">
									<?php echo esc_html_e( 'アーカイブ', 'wordpressfoundation' ); ?>
								</summary>

								<nav class="category-accordion__main" aria-label="<?php echo esc_attr_e( 'アーカイブ', 'wordpressfoundation' ); ?>">
									<?php echo $date_navigation; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</nav>
							</details>
						</div>
						<?php
					}
					?>
				</div>
				<?php
			}
			?>

			<div class="archive-blog__main">
				<?php
				if ( have_posts() ) {
					?>
					<div class="archive-blog__items">
						<?php
						while ( have_posts() ) {
							the_post();
							?>
							<article class="archive-blog__item">
								<div class="archive-blog__item__inner">
									<div class="archive-blog__item__main">
										<div class="archive-blog__item__header">
											<div class="archive-blog__item__header__inner">
												<div class="archive-blog__item__date">
													<?php echo $wpf_template_tags::get_the_publish_date_tag(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
												</div>

												<?php
												/**
												 * タイトル
												 */
												$wpf_title = get_the_title();
												if ( $wpf_title ) {
													?>
													<h2 class="archive-blog__item__title">
														<a 
															href="<?php the_permalink(); ?>"
															class="link-muted">
															<?php echo $wpf_title; // phpcs:ignore WordPress.Security.EscapeOutput ?>
														</a>
													</h2>
													<?php
												}
												?>
											</div>
										</div>

										<a href="<?php the_permalink(); ?>" class="archive-blog__item__thubmnail frame" aria-hidden="true" tabindex="-1">
											<?php $wpf_template_tags::the_image( get_post_thumbnail_id(), 'blog_thumbnail' ); ?>
										</a>

										<?php
										/**
										 * 抜粋
										 */
										$wpf_excerpt = get_the_excerpt();
										if ( $wpf_excerpt ) {
											?>
											<p class="archive-blog__item__excerpt">
												<?php echo $wpf_excerpt; // phpcs:ignore WordPress.Security.EscapeOutput ?>
											</p>
											<?php
										}
										?>

										<div class="archive-blog__item__cta text-center">
											<a href="<?php the_permalink(); ?>" class="button:secondary:with-icon">
												<span><?php echo esc_html_e( '記事を読む', 'wordpressfoundation' ); ?></span>
												<span><?php echo WPF_Icons::get_svg( 'ui', 'arrow_right', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
											</a>
										</div>
									</div>
								</div>
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
