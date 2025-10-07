<?php
/**
 * フッターテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

global $wpf_template_tags;
?>

	<footer class="site-footer">
		<div class="site-footer__inner">
			<div class="site-footer__logo">
				<?php
				get_template_part(
					'template-parts/site',
					'branding',
					array(
						'context' => 'site-footer',
						'svg'     => wpf_get_site_footer_logo(),
					)
				);
				?>
			</div>

			<div class="site-footer__nav-primary">
				<?php
				if ( has_nav_menu( 'footer_primary' ) ) {
					wp_nav_menu(
						array(
							'theme_location'       => 'footer_primary',
							'container'            => 'nav',
							'container_class'      => 'footer-nav-primary',
							'container_aria_label' => __( 'フッターナビゲーション', 'wordpressfoundation' ),
							'menu_class'           => 'navigation__list',
							'fallback_cb'          => false,
							'wpf_link_classes'     => 'link-muted',
						)
					);
				}
				?>
			</div>

			<div class="site-footer__nav-secondary">
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

				<?php
				if ( has_nav_menu( 'social_links' ) ) {
					wp_nav_menu(
						array(
							'theme_location'       => 'social_links',
							'container'            => 'div',
							'container_class'      => 'navigation social-links footer-social-links',
							'container_aria_label' => __( 'ソーシャルリンク', 'wordpressfoundation' ),
							'menu_class'           => 'navigation__list',
							'fallback_cb'          => false,
							'link_before'          => '<span>', // ソーシャルリンクをアイコンに置換するために必須。
							'link_after'           => '</span>',
							'depth'                => 1,
						)
					);
				}
				?>
			</div>

			<div class="site-footer__notice">
				<ul>
					<li>協生農法は株式会社桜自然塾の商標または登録商標です。</li>
					<li>Synecocultureはソニー株式会社の商標です。</li>
				</ul>
			</div>

			<div class="site-footer__copy-container">
				<?php echo do_shortcode( '[wpf_darkmode_switch]' ); ?>

				<p style="font: var(--font-text--xs)"><?php echo esc_html( $wpf_template_tags::get_copyright() ); ?></p>
			</div>
		</div>
	</footer>

	<?php wp_footer(); ?>
</body>
</html>
