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
