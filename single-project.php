<?php
/**
 * `project`投稿タイプの個別投稿ページテンプレート
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
				<?php
				/**
				 * サイドバー
				 */
				$related_members = get_post_meta( get_the_ID(), '_wpf_related_members', true );
				if ( ! empty( $related_members ) ) {
					$query = new WP_Query(
						array(
							'post_type'      => 'member',
							'posts_per_page' => -1,
							'post__in'       => $related_members,
							'orderby'        => array(
								'menu_order' => 'ASC',
								'name'       => 'ASC',
							),
						)
					);
					if ( $query->have_posts() ) {
						?>
						<div class="single-project__sidebar">
							<div class="related-members">
								<div class="related-members__header">
									<div class="syneco-overline">
										<div class="syneco-overline__icon">
											<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<div class="syneco-overline__text">Related Members</div>
									</div>
								</div>

								<div class="related-members__main">
									<?php
									while ( $query->have_posts() ) {
										$query->the_post();
										?>
										<div class="related-members__item">
											<a 
												href="<?php the_permalink(); ?>" 
												class="related-members__item__avatar"
												aria-label="<?php echo esc_attr( /* translators: %s: 投稿タイトル */ sprintf( __( '%sのプロフィールページへ', 'wordpressfoundation' ), $title ) ); ?>"
												aria-hidden="true"
												tabindex="-1">
												<?php $wpf_template_tags::the_member_image( get_post_thumbnail_id(), 'thumbnail' ); ?>
											</a>
											<div class="related-members__item__content">
												<a class="related-members__item__title" href="<?php the_permalink(); ?>">
													<?php the_title(); ?>
												</a>
												<?php
												$terms = WPF_Utils::get_the_terms();
												if ( ! empty( $terms ) && 'uncategorized' !== $terms[0]->slug ) {
													?>
													<a class="related-members__item__position" href="<?php the_permalink(); ?>" tabindex="-1">
														<?php echo esc_html( $terms[0]->name ); ?>
													</a>
													<?php
												}
												?>
											</div>
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
				}
				?>

				<div class="single-project__main">
					<div class="prose">
						<?php
						/**
						 * コンテンツ
						 */
						the_content();

						/**
						 * ロゴhr
						 */
						?>
						<div class="logo-hr" aria-hidden="true">
							<svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M33.3115 40.2734C35.3666 40.0259 37.0642 42.1094 37.2773 44.7158C37.6772 49.6035 37.6537 52.2837 36.4883 59.9844C33.1484 52.9807 32.184 49.4652 31.1348 45.3262C30.4204 42.505 31.4619 40.4953 33.3115 40.2734ZM39.9639 42.5996C41.2138 40.7336 43.99 40.677 46.7061 42.9326C50.6905 46.2422 54.0342 49.1326 59.9248 56.4102C50.8198 53.5934 47.8583 52.2042 42.6982 49.1953C39.9466 47.5914 38.5761 44.6714 39.9639 42.5996ZM13.6318 38.5254C16.4128 36.3492 19.1708 36.4574 20.3447 38.3232C21.6478 40.395 20.1792 43.2527 17.3857 44.7852C12.1466 47.6589 9.15233 48.9755 0 51.585C6.12166 44.5101 9.55044 41.7181 13.6318 38.5254ZM25.792 37.0166C27.2595 35.0683 29.1288 34.6558 30.2578 35.7012C31.5116 36.8624 31.0491 39.0377 29.4551 40.5566C26.4664 43.4052 24.319 45.6743 18.6562 49.0332C21.4683 43.2107 23.6386 39.8759 25.792 37.0166ZM34.0889 36.0615C34.3965 34.3984 36.5079 33.5894 38.6768 34.0684C42.7442 34.966 47.0687 36.0252 53.0322 38.8389C46.52 39.7107 41.2727 39.2163 37.6523 39.0098C35.1855 38.8691 33.8118 37.5589 34.0889 36.0615ZM6.84473 26.4004C14.7177 26.8705 18.8859 27.9156 23.0967 28.9902C25.9669 29.7223 27.2702 31.5794 26.5859 33.2783C25.8263 35.1651 23.1476 35.631 20.71 34.5693C16.1382 32.5789 13.191 31.0815 6.84473 26.4004ZM25.002 16.8975C29.0306 21.988 30.8721 25.4908 32.501 28.666C33.6111 30.8301 33.1407 32.6499 31.6797 33.165C30.0563 33.7354 28.2848 32.3479 27.623 30.2686C26.381 26.3675 25.4994 23.3661 25.002 16.8975ZM51.3525 18.2061C47.0256 24.6687 44.4803 27.3208 41.4365 30.3691C39.363 32.4461 37.0708 32.6329 35.9053 31.207C34.612 29.6237 35.5299 27.1137 37.6807 25.5674C41.714 22.6671 44.0547 21.2787 51.3525 18.2061ZM34.9805 0C37.3648 8.9766 37.7223 13.3316 38.04 18.4404C38.2561 21.9216 36.5811 24.0699 34.3486 23.9385C31.8698 23.791 30.4291 20.9701 30.7568 17.8496C31.3705 11.9968 31.8941 8.83824 34.9805 0Z" fill="var(--color-border-opaque)"/>
							</svg>
						</div>

						<?php
						// 戻るリンク
						$wpf_back_link = $wpf_template_tags::get_the_back_link();
						if ( $wpf_back_link ) {
							?>
							<div class="widget d-flex jc-center">
								<?php echo $wpf_back_link; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
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
