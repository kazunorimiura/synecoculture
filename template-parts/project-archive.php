<?php
/**
 * プロジェクトアーカイブテンプレート
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
		<div class="archive-project">
			<?php
			/**
			 * サイドバー
			 */

			// カテゴリー
			$top_level_project_cat_terms = get_terms(
				array(
					'taxonomy' => 'project_cat',
					'parent'   => 0,  // 親がないタームのみ取得
				)
			);
			$project_domain_terms        = get_terms(
				array(
					'taxonomy' => 'project_domain',
				)
			);
			if ( $top_level_project_cat_terms && ! is_wp_error( $top_level_project_cat_terms ) || $project_domain_terms && ! is_wp_error( $project_domain_terms ) ) {
				$current_term = WPF_Template_Tags::get_the_current_term();
				?>
				<div class="archive-project__sidebar">
					<?php
					if ( $top_level_project_cat_terms && ! is_wp_error( $top_level_project_cat_terms ) ) {
						$is_open = $current_term && 'project_domain' === $current_term->taxonomy ? '' : ' open';
						?>
						<div class="archive-project__sidebar-item">
							<details class="category-accordion" aria-label="<?php esc_attr_e( 'カテゴリー', 'wordpressfoundation' ); ?>"<?php echo esc_attr( $is_open ); ?>>
								<summary class="category-accordion__heading">
									<?php echo esc_html_e( 'カテゴリー', 'wordpressfoundation' ); ?>
								</summary>

								<nav class="category-accordion__main" aria-label="<?php echo esc_attr_e( 'カテゴリー', 'wordpressfoundation' ); ?>">
									<?php
									foreach ( $top_level_project_cat_terms as $project_cat_term ) {
										$aria_current_attr     = $current_term && $current_term->term_id === $project_cat_term->term_id ? ' aria-current="page"' : '';
										$project_cat_term_link = get_term_link( $project_cat_term );
										if ( ! is_wp_error( $project_cat_term_link ) ) {
											?>
											<a href="<?php echo esc_url( $project_cat_term_link ); ?>" class="category-accordion__item"<?php echo $aria_current_attr; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>>
												<?php echo esc_html( $project_cat_term->name ); ?>
											</a>
											<?php
										}
									}
									?>
								</nav>
							</details>
						</div>
						<?php
					}
					?>

					<?php
					if ( $project_domain_terms && ! is_wp_error( $project_domain_terms ) ) {
						$is_open = $current_term && 'project_domain' === $current_term->taxonomy ? ' open' : '';
						?>
						<div class="archive-project__sidebar-item">
							<details class="category-accordion" aria-label="<?php esc_attr_e( '領域', 'wordpressfoundation' ); ?>"<?php echo esc_attr( $is_open ); ?>>
								<summary class="category-accordion__heading">
									<?php echo esc_html_e( '領域', 'wordpressfoundation' ); ?>
								</summary>

								<nav class="category-accordion__main" aria-label="<?php echo esc_attr_e( '領域', 'wordpressfoundation' ); ?>">
									<?php
									foreach ( $project_domain_terms as $project_domain_term ) {
										$aria_current_attr        = $current_term && $current_term->term_id === $project_domain_term->term_id ? ' aria-current="page"' : '';
										$project_domain_term_link = get_term_link( $project_domain_term );
										if ( ! is_wp_error( $project_domain_term_link ) ) {
											?>
											<a href="<?php echo esc_url( $project_domain_term_link ); ?>" class="category-accordion__item"<?php echo $aria_current_attr; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>>
												<?php echo esc_html( $project_domain_term->name ); ?>
											</a>
											<?php
										}
									}
									?>
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

			<?php
			if ( have_posts() ) {
				?>
				<div class="archive-project__main">
					<?php
					while ( have_posts() ) {
						the_post();

						$cat_terms    = get_the_terms( get_the_ID(), 'project_cat' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
						$domain_terms = get_the_terms( get_the_ID(), 'project_domain' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
						?>
						<article class="archive-project__item">
							<div class="archive-project__item__inner">
								<div class="archive-project__item__main">
									<?php
									/**
									 * タイトル
									 */
									$wpf_title = get_the_title();
									if ( $wpf_title ) {
										?>
										<h2 class="archive-project__item__title">
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
										<p class="archive-project__item__excerpt">
											<?php echo $wpf_excerpt; // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</p>
										<?php
									}
									?>

									<?php
									/**
									 * 選択しているタームと領域タームを出力
									 */
									if ( ( $cat_terms && ! is_wp_error( $cat_terms ) ) || ( $domain_terms && ! is_wp_error( $domain_terms ) ) ) {
										?>
										<div class="archive-project__item__sub-categories">
											<?php
											if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
												foreach ( $cat_terms as $term ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride
													$term_link = get_term_link( $term->term_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
													?>
													<a href="<?php echo esc_url( $term_link ); ?>" class="archive-project__item__sub-category pill-secondary">
														<?php echo esc_html( $term->name ); ?>
													</a>
													<?php
												}
											}

											if ( $domain_terms && ! is_wp_error( $domain_terms ) ) {
												foreach ( $domain_terms as $term ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride
													$term_link = get_term_link( $term->term_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
													?>
													<a href="<?php echo esc_url( $term_link ); ?>" class="archive-project__item__sub-category pill-secondary">
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

								<div class="archive-project__item__header">
									<?php
									if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
										?>
										<div class="archive-project__item__main-categories">
											<?php
											// 選択しているタームの最祖先を出力
											foreach ( $cat_terms as $term ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride
												// 祖先タームのIDを配列で取得（最も近い親から最上位の順）
												$ancestors = get_ancestors( $term->term_id, 'project_cat', 'taxonomy' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

												if ( ! empty( $ancestors ) ) {
													// 配列の最後が最上位のターム
													$top_parent_id   = end( $ancestors ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
													$top_parent      = get_term( $top_parent_id, 'project_cat' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
													$top_parent_link = get_term_link( $top_parent ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
													?>
													<a href="<?php echo esc_url( $top_parent_link ); ?>" class="archive-project__item__main-category pill">
														<?php echo esc_html( $top_parent->name ); ?>
													</a>
													<?php
												} else {
													// 祖先がいない場合は自身が最上位
													$term_link = get_term_link( $term->term_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
													?>
													<a href="<?php echo esc_url( $term_link ); ?>" class="archive-project__item__main-category pill">
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

									<a href="<?php the_permalink(); ?>" class="archive-project__item__thubmnail frame" aria-hidden="true" tabindex="-1">
										<?php $wpf_template_tags::the_image( get_post_thumbnail_id() ); ?>
									</a>
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
							'%s <span>%s</span>',
							WPF_Icons::get_svg( 'ui', 'angle_left' ),
							esc_html__( '前へ', 'wordpressfoundation' )
						),
						'next_text'          => sprintf(
							'<span>%s</span> %s',
							esc_html__( '次へ', 'wordpressfoundation' ),
							WPF_Icons::get_svg( 'ui', 'angle_right' )
						),
						'before_page_number' => '<span class="screen-reader-text">' . __( '投稿', 'wordpressfoundation' ) . ' </span>',
					)
				);
			} else {
				?>
				<p><?php esc_html_e( '投稿が見つかりませんでした。', 'wordpressfoundation' ); ?></p>
				<?php
			}
			?>
		</div>
	</div>
</main>

<?php
get_footer();
