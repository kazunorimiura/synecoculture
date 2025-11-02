<?php
/**
 * ページヘッダー
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

global $wpf_template_tags;

$wpf_breadcrumbs       = WPF_Template_Tags::get_the_breadcrumbs( 'display' );
$wpf_cover_media       = WPF_Template_Tags::get_the_cover_media();
$wpf_container_classes = $wpf_cover_media && ! empty( get_object_vars( $wpf_cover_media ) ) ? 'page-header page-header--has-cover' : 'page-header';
$wpf_subtitle          = isset( $args['subtitle'] ) ? $args['subtitle'] : '';
?>

<div class="<?php echo esc_attr( $wpf_container_classes ); ?>">
	<div class="page-header__main">
		<div class="page-header__main__inner">
			<?php
			// 検索結果ページの場合
			if ( is_search() ) {
				?>
				<div class="wrapper:wide border-bottom">
					<div class="flow">
						<h1 class="page-header__title">
							<?php esc_html_e( 'Search', 'wordpressfoundation' ); ?>
						</h1>

						<?php get_search_form(); ?>
					</div>
				</div>
				<?php

				// 上記以外のページの場合
			} else {
				?>
				<div class="page-header__main__content__container">
					<div class="page-header__main__content">
						<?php
						/**
						 * ヘッダーメタ
						 */
						if ( ! is_singular( 'member' ) ) {
							$wpf_terms = WPF_Utils::get_the_terms();
							if ( ! is_single() && $wpf_breadcrumbs || is_singular( 'manual' ) && $wpf_breadcrumbs || is_single() && ! empty( $wpf_terms ) && 'uncategorized' !== $wpf_terms[0]->slug ) {
								?>
								<?php
								/**
								 * アーカイブページにおけるパンくずリスト
								 */
								if ( ! is_single() && $wpf_breadcrumbs || is_singular( 'manual' ) && $wpf_breadcrumbs ) {
									?>
									<div class="page-header__breadcrumbs-container">
										<nav class="breadcrumbs" aria-label="<?php esc_html_e( 'パンくずリスト', 'wordpressfoundation' ); ?>">
											<ul class="breadcrumbs__list">
												<?php
												foreach ( $wpf_breadcrumbs as $wpf_breadcrumb ) {
													?>
													<li>
														<?php
														if ( end( $wpf_breadcrumbs ) === $wpf_breadcrumb ) {
															echo $wpf_breadcrumb['text']; // phpcs:ignore WordPress.Security.EscapeOutput
														} else {
															?>
															<a href="<?php echo esc_url( $wpf_breadcrumb['link'] ); ?>">
																<?php echo $wpf_breadcrumb['text']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
															</a>
															<?php
														}
														?>
													</li>
													<?php
												}
												?>
											</ul>
										</nav>
									</div>
									<?php
								}
								?>

								<?php
								/**
								 * タームリスト
								 */
								if ( is_single() && ! empty( $wpf_terms ) && 'uncategorized' !== $wpf_terms[0]->slug ) {
									// `project` 投稿タイプの場合
									if ( is_singular( 'project' ) ) {
										$ancestors = get_ancestors( $wpf_terms[0]->term_id, 'project_cat', 'taxonomy' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
										if ( ! empty( $ancestors ) ) {
											// 配列の最後が最上位のターム
											$top_parent_id   = end( $ancestors ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
											$top_parent      = get_term( $top_parent_id, 'project_cat' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
											$top_parent_link = get_term_link( $top_parent );  // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
											?>
											<a href="<?php echo esc_url( $top_parent_link ); ?>" class="pill">
												<?php echo esc_html( $top_parent->name ); ?>
											</a>
											<?php
										} else {
											// 祖先がいない場合は自身が最上位
											$term_link = get_term_link( $term->term_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
											?>
											<a href="<?php echo esc_url( $term_link ); ?>" class="pill">
												<?php echo esc_html( $term->name ); ?>
											</a>
											<?php
										}

										// project投稿タイプ以外の場合
									} else {
										?>
										<a href="<?php echo esc_url( get_term_link( $wpf_terms[0]->term_id, $wpf_terms[0]->taxonomy ) ); ?>" class="pill">
											<?php echo esc_html( $wpf_terms[0]->name ); ?>
										</a>
										<?php
									}
								}
								?>
								<?php
							}
						}
						?>

						<?php
						/**
						 * タイトル
						 */
						$wpf_title = WPF_Template_Tags::get_the_page_title();
						if ( ! empty( $wpf_title ) ) {
							if ( is_singular( 'manual' ) ) {
								?>
								<p class="page-header__title">
									<?php echo $wpf_title; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</p>
								<?php
							} else {
								?>
								<h1 class="page-header__title">
									<?php echo $wpf_title; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</h1>
								<?php
							}
						}
						?>

						<?php
						/**
						 * サブタイトル
						 */
						if ( ! is_archive() && $wpf_subtitle ) {
							?>
							<p class="page-header__subtitle">
								<?php echo wp_kses_post( $wpf_subtitle ); ?>
							</p>
							<?php
						}
						?>

						<?php
						/**
						 * ニュース、ブログ個別投稿ページにおけるフッターメタ
						 */
						if ( is_single() && is_singular( 'post' ) || is_singular( 'blog' ) ) {
							?>
							<div class="page-header__footer-meta">
								<div class="page-header__date">
									<?php
									echo WPF_Template_Tags::get_the_publish_date_tag(); // phpcs:ignore WordPress.Security.EscapeOutput
									?>
								</div>
							</div>
							<?php
						}

						/**
						 * プロジェクト個別投稿ページにおけるフッターメタ
						 */
						if ( is_single() && is_singular( 'project' ) ) {
							$cat_terms    = get_the_terms( get_the_ID(), 'project_cat' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
							$domain_terms = get_the_terms( get_the_ID(), 'project_domain' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals

							// 選択しているタームを出力
							if ( ( $cat_terms && ! is_wp_error( $cat_terms ) ) || ( $domain_terms && ! is_wp_error( $domain_terms ) ) ) {
								?>
								<div class="page-header__footer-meta cluster" style="--cluster-space: 0.5rem;">
									<?php
									if ( $cat_terms && ! is_wp_error( $cat_terms ) ) {
										foreach ( $cat_terms as $term ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride
											$term_link = get_term_link( $term->term_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
											?>
											<a href="<?php echo esc_url( $term_link ); ?>" class="pill-secondary">
												<?php echo esc_html( $term->name ); ?>
											</a>
											<?php
										}
									}

									if ( $domain_terms && ! is_wp_error( $domain_terms ) ) {
										foreach ( $domain_terms as $term ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride
											$term_link = get_term_link( $term->term_id ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals
											?>
											<a href="<?php echo esc_url( $term_link ); ?>" class="pill-secondary">
												<?php echo esc_html( $term->name ); ?>
											</a>
											<?php
										}
									}
									?>
								</div>
								<?php
							}
						}
						?>

						<?php
						/**
						 * メンバー個別投稿ページにおけるフッターメタ
						 */
						if ( is_singular( 'member' ) ) {
							$wpf_terms = WPF_Utils::get_the_terms();
							if ( ! empty( $wpf_terms ) && 'uncategorized' !== $wpf_terms[0]->slug ) {
								?>
								<div class="page-header__footer-meta" style="--flow-space: var(--space-s-5)">
									<div class="font-article fw-sign">
										<?php echo esc_html( $wpf_terms[0]->name ); ?>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>

					<?php
					/**
					 * サブタイトル（アーカイブページ）
					 */
					if ( is_archive() && $wpf_subtitle ) {
						?>
						<p class="page-header__subtitle">
							<?php echo wp_kses_post( $wpf_subtitle ); ?>
						</p>
						<?php
					}
					?>
				</div>

				<?php
				/**
				 * シングルページにおけるサムネイル
				 */
				if ( is_single() && ! is_singular( 'member' ) && ! is_singular( 'manual' ) ) {
					?>
					<div class="page-header__thumbnail frame">
						<?php echo $wpf_template_tags::the_image( get_post_thumbnail_id(), 'page-header-thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<?php
				} elseif ( is_single() && is_singular( 'member' ) ) {
					?>
					<div class="page-header__thumbnail frame">
						<?php echo $wpf_template_tags::the_member_image( get_post_thumbnail_id() ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<?php
				}
			}
			?>
		</div>
	</div>

	<?php
	/**
	 * シングルページにおけるパンくずリスト
	 */
	if ( is_single() && $wpf_breadcrumbs && ! is_singular( 'manual' ) ) {
		?>
		<div class="page-header__breadcrumbs-container">
			<nav class="breadcrumbs" aria-label="<?php esc_html_e( 'パンくずリスト', 'wordpressfoundation' ); ?>">
				<ul class="breadcrumbs__list">
					<?php
					foreach ( $wpf_breadcrumbs as $wpf_breadcrumb ) {
						?>
						<li>
							<?php
							if ( end( $wpf_breadcrumbs ) === $wpf_breadcrumb ) {
								echo $wpf_breadcrumb['text']; // phpcs:ignore WordPress.Security.EscapeOutput
							} else {
								?>
								<a href="<?php echo esc_url( $wpf_breadcrumb['link'] ); ?>">
									<?php echo $wpf_breadcrumb['text']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</a>
								<?php
							}
							?>
						</li>
						<?php
					}
					?>
				</ul>
			</nav>
		</div>
		<?php
	}
	?>

	<?php
	if ( ! empty( $wpf_cover_media ) ) {
		?>
		<div class="page-header__cover">
			<?php
			/**
			 * カバー画像の場合
			 */
			if ( isset( $wpf_cover_media->media_metadata ) && isset( $wpf_cover_media->media_metadata->type ) && 'image' === $wpf_cover_media->media_metadata->type ) {
				$wpf_cover_media_image = wp_get_attachment_image(
					$wpf_cover_media->media_id,
					'stretch',
					false,
					array()
				);
				if ( ! empty( $wpf_cover_media_image ) ) {
					?>
					<div class="page-header__cover-image">
						<?php echo $wpf_cover_media_image; // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</div>
					<?php
				}

				/**
				 * カバー動画の場合
				 */
			} elseif ( isset( $wpf_cover_media->media_metadata ) && isset( $wpf_cover_media->media_metadata->type ) && 'video' === $wpf_cover_media->media_metadata->type ) {
				?>
				<div class="page-header__cover-video">
					<video autoplay muted playsinline loop>
						<source src="<?php echo esc_url( wp_get_attachment_url( $wpf_cover_media->media_id ) ); ?>" type="<?php echo esc_attr( $wpf_cover_media->media_metadata->mime ); ?>">
					</video>
				</div>
				<?php
			}
			?>
		</div>
		<?php
	}
	?>
</div>
