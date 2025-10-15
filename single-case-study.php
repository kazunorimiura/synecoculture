<?php
/**
 * `case-study`投稿タイプの個別投稿ページテンプレート
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
				$wpf_show_toc = get_post_meta( get_the_ID(), '_wpf_show_toc', true );
				if ( $wpf_show_toc ) {
					$wpf_toc      = new WPF_Toc();
					$wpf_content  = $wpf_toc->get_the_content();
					$wpf_toc_menu = $wpf_toc->get_html_menu( $wpf_content );

					if ( $wpf_toc_menu ) {
						/**
						 * 目次（デスクトップ）
						 */
						?>
						<div class="single-case-study__sidebar">
							<nav class="singular__toc toc flow over-scroll lg:hidden-yes" aria-label="<?php esc_attr_e( 'Index', 'wordpressfoundation' ); ?>">
								<?php echo $wpf_toc_menu; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</nav>
						</div>
						<?php
					}
				}
				?>

				<div class="single-case-study__main">
					<div class="prose">
						<?php
						/**
						 * 目次（モバイル）
						 */
						if ( $wpf_show_toc && $wpf_toc_menu ) {
							?>
							<div class="singular__toc:fold hidden-yes lg:hidden-no">
								<details class="toc flow" aria-label="<?php esc_attr_e( 'Index', 'wordpressfoundation' ); ?>">
									<summary class="text-upper"><?php esc_html_e( 'Index', 'wordpressfoundation' ); ?></summary>
									<?php echo $wpf_toc_menu; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</details>
							</div>
							<?php
						}
						?>

						<?php
						/**
						 * コンテンツ
						 */
						if ( $wpf_show_toc ) {
							echo $wpf_content; // phpcs:ignore WordPress.Security.EscapeOutput
						} else {
							the_content();
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
