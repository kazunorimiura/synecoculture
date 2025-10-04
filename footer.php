<?php
/**
 * フッターテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

global $wpf_template_tags;
?>

	<footer class="site-footer wrapper:stretch flow border-top bg-color-background-secondary" style="--repel-vertical-align: normal">
		<div class="site-footer__inner region pos:rel" style="--region-space: var(--space-s5)">
			<a class="page-top" href="#page-top">
				<span class="screen-reader-text"><?php esc_html_e( '上へ戻る', 'wordpressfoundation' ); ?></span>
				<?php echo WPF_Icons::get_svg( 'ui', 'angle_up', 24 ); /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
			</a>

			<div class="flow">
				<div class="repel" style="--repel-vertical-align: flex-start">
					<?php
					if ( is_active_sidebar( 'site-footer-widget' ) ) {
						?>
						<div class="site-footer-widget cluster" style="--cluster-align-items: flex-start; --cluster-space: var(--space-s6)">
							<?php
							dynamic_sidebar( 'site-footer-widget' );
							?>
						</div>
						<?php
					}

					if ( has_nav_menu( 'footer_primary' ) ) {
						wp_nav_menu(
							array(
								'theme_location'       => 'footer_primary',
								'container'            => 'nav',
								'container_class'      => 'navigation dropdown-menu footer-nav-primary',
								'container_aria_label' => __( 'フッターナビゲーション', 'wordpressfoundation' ),
								'menu_class'           => 'navigation__list',
								'fallback_cb'          => false,
								'walker'               => new WPF_Walker_Nav_Menu(),
								'wpf_link_classes'     => 'link-muted',
							)
						);
					} else {
						wp_page_menu(
							array(
								'container'        => 'nav',
								'menu_class'       => 'navigation page-menu dropdown-menu',
								'walker'           => new WPF_Walker_Page(),
								'wpf_link_classes' => 'link-muted',
							)
						);
					}
					?>
				</div>

				<div class="repel region" style="--region-space: var(--space-s4); --repel-vertical-align: flex-end; --flow-space: 0">
					<div class="flow" style="--flow-space: var(--space-s0)">
						<?php
						if ( has_nav_menu( 'footer_secondary' ) ) {
							wp_nav_menu(
								array(
									'theme_location'       => 'footer_secondary',
									'container'            => 'nav',
									'container_class'      => 'navigation dropdown-menu footer-nav-secondary',
									'container_aria_label' => __( 'サブフッターナビゲーション', 'wordpressfoundation' ),
									'menu_class'           => 'navigation__list',
									'fallback_cb'          => false,
									'walker'               => new WPF_Walker_Nav_Menu(),
									'wpf_link_classes'     => 'link-muted',
								)
							);
						}
						?>

						<div class="cluster">
							<?php
							if ( has_nav_menu( 'social_links' ) ) {
								wp_nav_menu(
									array(
										'theme_location'  => 'social_links',
										'container'       => 'div',
										'container_class' => 'navigation social-links footer-social-links',
										'container_aria_label' => __( 'ソーシャルリンク', 'wordpressfoundation' ),
										'menu_class'      => 'navigation__list',
										'fallback_cb'     => false,
										'link_before'     => '<span>', // ソーシャルリンクをアイコンに置換するために必須。
										'link_after'      => '</span>',
										'depth'           => 1,
									)
								);
							}
							?>

							<p style="font: var(--font-text--xs)"><?php echo esc_html( $wpf_template_tags::get_copyright() ); ?></p>
						</div>
					</div>
				</div>

				<div class="repel" style="--flow-space: 0">
					<?php echo do_shortcode( '[wpf_darkmode_switch]' ); ?>

					<?php
					get_template_part(
						'template-parts/site',
						'branding',
						array(
							'context' => 'site-footer',
							'svg'     => wpf_get_site_logo(),
						)
					);
					?>
				</div>
			</div>
		</div>
	</footer>

	<?php wp_footer(); ?>
</body>
</html>
