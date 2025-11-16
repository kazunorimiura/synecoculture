<?php
/**
 * `member`投稿タイプの個別投稿ページテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals,WordPress.WP.GlobalVariablesOverride

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

			<div class="page-main">
				<div class="single-member__main">
					<?php
					/**
					 * Biography
					 */
					$content = get_the_content();

					if ( ! empty( $content ) ) {
						?>
						<div class="single-member__item">
							<div class="single-member__item__header__container">
								<div class="single-member__item__header">
									<div class="syneco-overline">
										<div class="syneco-overline__icon">
											<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<div class="syneco-overline__text">Biography</div>
									</div>
								</div>
							</div>

							<div class="single-member__item__main">
								<div class="prose">
									<?php
									/**
									 * コンテンツ
									 */
									the_content();
									?>

									<?php
									/**
									 * ソーシャルリンク
									 */
									$member_social_links = SCF::get( '_wpf_member_social_links' );
									if ( isset( $member_social_links ) && ! empty( $member_social_links[0]['_wpf_member_social_links__url'] ) ) {
										?>
										<div class="member-social-links">
											<?php
											foreach ( $member_social_links as $member_social_link ) {
												$url  = $member_social_link['_wpf_member_social_links__url'];
												$name = $member_social_link['_wpf_member_social_links__name'];
												$icon = $member_social_link['_wpf_member_social_links__icon'];
												if ( ! empty( $url ) ) {
													$svg = WPF_Icons::get_social_link_svg( $url );
													?>
													<a class="member-social-link button:primary:icon" href="<?php echo esc_url( $url ); ?>" target="_blank">
														<?php
														if ( ! empty( $icon ) ) {
															echo $icon . '<span class="screen-reader-text">'; // phpcs:ignore WordPress.Security.EscapeOutput
														} else {
															echo $svg; // phpcs:ignore WordPress.Security.EscapeOutput
														}
														?>

														<?php
														if ( ! empty( $name ) ) {
															?>
															<?php echo esc_html( $name ); ?></span>
															<?php
														}
														?>
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
							</div>
						</div>
						<?php
					}
					?>

					<?php
					/**
					 * Projects
					 */
					$projects = WPF_Posts::get_posts(
						'projects',
						array(
							'post_type'      => 'project',
							'posts_per_page' => 3,
							'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
							'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								array(
									'key'     => '_wpf_related_members',
									'value'   => wp_json_encode( get_the_ID() ),
									'compare' => 'LIKE',
								),
							),
							'orderby'        => array(
								'date' => 'DESC',
							),
						),
						true
					);

					if ( ! empty( $projects ) ) {
						?>
						<div class="single-member__item">
							<div class="single-member__item__header__container">
								<div class="single-member__item__header">
									<div class="syneco-overline">
										<div class="syneco-overline__icon">
											<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<div class="syneco-overline__text">Projects</div>
									</div>
								</div>
							</div>

							<div class="single-member__item__main">
								<div class="prose">
									<?php
									/**
									 * コンテンツ
									 */
									echo $projects; // phpcs:ignore WordPress.Security.EscapeOutput
									?>
								</div>
							</div>
						</div>
						<?php
					}
					?>

					<?php
					/**
					 * Blog
					 */
					$blog = WPF_Posts::get_posts(
						'news_blog',
						array(
							'post_type'      => 'blog',
							'posts_per_page' => 3,
							'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
							'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								array(
									'key'     => '_wpf_related_members',
									'value'   => wp_json_encode( get_the_ID() ),
									'compare' => 'LIKE',
								),
							),
							'orderby'        => array(
								'date' => 'DESC',
							),
						),
						true
					);

					if ( ! empty( $blog ) ) {
						?>
						<div class="single-member__item">
							<div class="single-member__item__header__container">
								<div class="single-member__item__header">
									<div class="syneco-overline">
										<div class="syneco-overline__icon">
											<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<div class="syneco-overline__text">Blog</div>
									</div>
								</div>
							</div>

							<div class="single-member__item__main">
								<div class="prose">
									<?php
									/**
									 * コンテンツ
									 */
									echo $blog; // phpcs:ignore WordPress.Security.EscapeOutput
									?>
								</div>
							</div>
						</div>
						<?php
					}
					?>

					<?php
					/**
					 * News
					 */
					$news = WPF_Posts::get_posts(
						'news_blog',
						array(
							'post_type'      => 'post',
							'posts_per_page' => 3,
							'paged'          => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
							'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
								array(
									'key'     => '_wpf_related_members',
									'value'   => wp_json_encode( get_the_ID() ),
									'compare' => 'LIKE',
								),
							),
							'orderby'        => array(
								'date' => 'DESC',
							),
						),
						true
					);

					if ( ! empty( $news ) ) {
						?>
						<div class="single-member__item">
							<div class="single-member__item__header__container">
								<div class="single-member__item__header">
									<div class="syneco-overline">
										<div class="syneco-overline__icon">
											<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<div class="syneco-overline__text">News</div>
									</div>
								</div>
							</div>

							<div class="single-member__item__main">
								<div class="prose">
									<?php
									/**
									 * コンテンツ
									 */
									echo $news; // phpcs:ignore WordPress.Security.EscapeOutput
									?>
								</div>
							</div>
						</div>
						<?php
					}
					?>

					<?php
					// 戻るリンク
					$wpf_back_link = $wpf_template_tags::get_the_back_link();
					if ( $wpf_back_link ) {
						?>
						<div class="widget d-flex jc-center" style="--flow-space: var(--space-s6)">
							<?php echo $wpf_back_link; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</main>
		<?php
	}
}

get_footer();
