<?php
/**
 * `case-study`投稿タイプのアーカイブテンプレート
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
		<?php
		if ( have_posts() ) {
			?>
			<div class="archive-case-study">
				<?php
				/**
				 * サイドバー
				 */

				// 地域
				$top_level_area_terms = get_terms(
					array(
						'taxonomy' => 'area',
						'parent'   => 0,  // 親がないタームのみ取得
					)
				);

				if ( $top_level_area_terms && ! is_wp_error( $top_level_area_terms ) ) {
					$current_term = WPF_Template_Tags::get_the_current_term();
					?>
					<div class="archive-case-study__sidebar">
						<?php
						if ( $top_level_area_terms && ! is_wp_error( $top_level_area_terms ) ) {
							$is_open = ' open';
							?>
							<div class="archive-case-study__sidebar-item">
								<details class="category-accordion" aria-label="<?php esc_attr_e( '地域', 'wordpressfoundation' ); ?>"<?php echo esc_attr( $is_open ); ?>>
									<summary class="category-accordion__heading">
										<?php echo esc_html_e( '地域', 'wordpressfoundation' ); ?>
									</summary>

									<nav class="category-accordion__main" aria-label="<?php echo esc_attr_e( '地域', 'wordpressfoundation' ); ?>">
										<ul>
											<?php
											foreach ( $top_level_area_terms as $area_term ) {
												$aria_current_attr = $current_term && $current_term->term_id === $area_term->term_id ? ' aria-current="page"' : '';
												$area_term_link    = get_term_link( $area_term );
												if ( ! is_wp_error( $area_term_link ) ) {
													?>
													<li>
														<a href="<?php echo esc_url( $area_term_link ); ?>" class="category-accordion__item"<?php echo $aria_current_attr; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>>
															<?php echo esc_html( $area_term->name ); ?>
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
					</div>
					<?php
				}
				?>

				<div class="archive-case-study__main">
					<div class="archive-case-study__items">
						<?php
						while ( have_posts() ) {
							the_post();

							$cat_terms = get_the_terms( get_the_ID(), 'area' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
							?>
							<article class="archive-case-study__item">
								<div class="archive-case-study__item__inner">
									<div class="archive-case-study__item__main">
										<?php
										if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
											?>
											<div class="archive-case-study__item__main-categories">
												<?php
												// 選択しているタームの最祖先を出力
												foreach ( $cat_terms as $term ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride
													// 祖先タームのIDを配列で取得（最も近い親から最上位の順）
													$ancestors = get_ancestors( $term->term_id, 'area', 'taxonomy' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

													if ( ! empty( $ancestors ) ) {
														// 配列の最後が最上位のターム
														$top_parent_id   = end( $ancestors ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
														$top_parent      = get_term( $top_parent_id, 'area' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
														$top_parent_link = get_term_link( $top_parent ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
														?>
														<a href="<?php echo esc_url( $top_parent_link ); ?>" class="archive-case-study__item__main-category">
															<?php echo WPF_Icons::get_svg( 'ui', 'map_marker' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
															<?php echo esc_html( $top_parent->name ); ?>
														</a>
														<?php
													} else {
														// 祖先がいない場合は自身が最上位
														$term_link = get_term_link( $term->term_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
														?>
														<a href="<?php echo esc_url( $term_link ); ?>" class="archive-case-study__item__main-category">
															<?php echo WPF_Icons::get_svg( 'ui', 'map_marker' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
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

										<?php
										/**
										 * タイトル
										 */
										$wpf_title = get_the_title();
										if ( $wpf_title ) {
											?>
											<h2 class="archive-case-study__item__title">
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
										 * 抜粋
										 */
										$wpf_excerpt = get_the_excerpt();
										if ( $wpf_excerpt ) {
											?>
											<p class="archive-case-study__item__excerpt">
												<?php echo $wpf_excerpt; // phpcs:ignore WordPress.Security.EscapeOutput ?>
											</p>
											<?php
										}
										?>
									</div>

									<a href="<?php the_permalink(); ?>" class="archive-case-study__item__thubmnail frame" aria-hidden="true" tabindex="-1">
										<?php $wpf_template_tags::the_image( get_post_thumbnail_id(), 'case_study_thumbnail' ); ?>
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
				</div>
			</div>
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
</main>

<?php
get_footer();
