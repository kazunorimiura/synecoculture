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
					<div class="prose">
						<?php
						/**
						 * 本文
						 */
						the_content();
						?>

						<?php
						/**
						 * 募集要項
						 */
						$career_items = SCF::get( '_wpf_career' );
						if ( ! WPF_Utils::is_array_empty( $career_items ) ) {
							?>
							<dl style="--flow-space: var(--space-s6)">
								<?php
								foreach ( $career_items as $item ) {
									$heading = $item['_wpf_career__title'];
									$body    = $item['_wpf_career__body'];
									if ( ! empty( $heading ) && ! empty( $body ) ) {
										?>
										<dt>
											<?php echo esc_html( $heading ); ?>
										</dt>
										<dd>
											<?php echo wp_kses_post( $body ); ?>
										</dd>
										<?php
									}
								}
								?>
							</dl>
							<?php
						}
						?>

						<?php
						/**
						 * 応募ボタン
						 */
						$contact_page = get_page_by_path( 'contact' );
						if ( $contact_page ) {
							$contact_page_id = $contact_page->ID;
							if ( function_exists( 'pll_get_post' ) ) {
								$contact_page_id = pll_get_post( $contact_page_id );

								if ( $contact_page->ID !== $contact_page_id ) {
									$contact_page = get_post( $contact_page_id );
								}
							}
							$permalink = esc_url( get_permalink( $contact_page->ID ) . '?inquiry-type=careers' );
							?>
							<div class="text-center" style="--flow-space: var(--space-s5)">
								<a href="<?php echo esc_url( $permalink ); ?>" class="button:primary">
									<span><?php echo esc_html_e( '応募する', 'wordpressfoundation' ); ?></span>
								</a>
							</div>
							<?php
						}
						?>
					</div>

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
