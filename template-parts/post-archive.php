<?php
/**
 * `post`投稿タイプのアーカイブテンプレート
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
		<div class="archive-post">
			<?php
			/**
			 * サイドバー
			 */

			// カテゴリー
			$top_level_category_terms = get_terms(
				array(
					'taxonomy' => 'category',
					'parent'   => 0,  // 親がないタームのみ取得
				)
			);

			// 日付アーカイブ
			$date_navigation = $wpf_template_tags::get_date_navigation();

			if ( $top_level_category_terms && ! is_wp_error( $top_level_category_terms ) || ! empty( $date_navigation ) ) {
				$current_term = WPF_Template_Tags::get_the_current_term();
				?>
				<div class="archive-post__sidebar">
					<?php
					if ( $top_level_category_terms && ! is_wp_error( $top_level_category_terms ) ) {
						$is_open = is_date() ? '' : ' open';
						?>
						<div class="archive-post__sidebar-item">
							<details class="category-accordion" aria-label="<?php esc_attr_e( 'カテゴリー', 'wordpressfoundation' ); ?>"<?php echo esc_attr( $is_open ); ?>>
								<summary class="category-accordion__heading">
									<?php echo esc_html_e( 'カテゴリー', 'wordpressfoundation' ); ?>
								</summary>

								<nav class="category-accordion__main" aria-label="<?php echo esc_attr_e( 'カテゴリー', 'wordpressfoundation' ); ?>">
									<ul>
										<?php
										foreach ( $top_level_category_terms as $category_term ) {
											$aria_current_attr  = $current_term && $current_term->term_id === $category_term->term_id ? ' aria-current="page"' : '';
											$category_term_link = get_term_link( $category_term );
											if ( ! is_wp_error( $category_term_link ) ) {
												?>
												<li>
													<a href="<?php echo esc_url( $category_term_link ); ?>" class="category-accordion__item"<?php echo $aria_current_attr; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>>
														<?php echo esc_html( $category_term->name ); ?>
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
						<div class="archive-post__sidebar-item">
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

			<div class="archive-post__main">
				<?php
				if ( have_posts() ) {
					?>
					<div class="archive-post__items">
						<?php
						while ( have_posts() ) {
							the_post();

							$cat_terms    = get_the_terms( get_the_ID(), 'category' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
							$domain_terms = get_the_terms( get_the_ID(), 'project_domain' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
							?>
							<article class="archive-post__item">
								<div class="archive-post__item__inner">
									<div class="archive-post__item__main">
										<?php
										/**
										 * タイトル
										 */
										$wpf_title = get_the_title();
										if ( $wpf_title ) {
											?>
											<h2 class="archive-post__item__title">
												<a 
													href="<?php the_permalink(); ?>"
													class="link-muted">
													<?php echo $wpf_title; // phpcs:ignore WordPress.Security.EscapeOutput ?>
												</a>
											</h2>
											<?php
										}
										?>

										<div class="archive-post__item__date">
											<?php echo $wpf_template_tags::get_the_publish_date_tag(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>

										<?php
										/**
										 * 選択しているタームと領域タームを出力
										 */
										if ( ( $cat_terms && ! is_wp_error( $cat_terms ) ) || ( $domain_terms && ! is_wp_error( $domain_terms ) ) ) {
											?>
											<div class="archive-post__item__sub-categories">
												<?php
												// 選択しているタームの最祖先を出力
												foreach ( $cat_terms as $term ) {
													// 祖先タームのIDを配列で取得（最も近い親から最上位の順）
													$ancestors = get_ancestors( $term->term_id, 'category', 'taxonomy' );

													if ( ! empty( $ancestors ) ) {
														// 配列の最後が最上位のターム
														$top_parent_id   = end( $ancestors );
														$top_parent      = get_term( $top_parent_id, 'category' );
														$top_parent_link = get_term_link( $top_parent );
														?>
														<a href="<?php echo esc_url( $top_parent_link ); ?>" class="archive-post__item__sub-category pill-secondary">
															<?php echo esc_html( $top_parent->name ); ?>
														</a>
														<?php
													} else {
														// 祖先がいない場合は自身が最上位
														$term_link = get_term_link( $term->term_id );
														?>
														<a href="<?php echo esc_url( $term_link ); ?>" class="archive-post__item__sub-category pill-secondary">
															<?php echo esc_html( $term->name ); ?>
														</a>
														<?php
													}
												}
												?>
											</div>
											<?php
										}
										?>
									</div>

									<a href="<?php the_permalink(); ?>" class="archive-post__item__thubmnail frame" aria-hidden="true" tabindex="-1">
										<?php $wpf_template_tags::the_image( get_post_thumbnail_id(), 'medium-large' ); ?>
									</a>
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
