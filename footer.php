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
				<?php
				$wpf_kyoseinoho_text    = function_exists( 'pll__' ) ? pll__( '協生農法は株式会社桜自然塾の商標または登録商標です。' ) : __( 'Se協生農法は株式会社桜自然塾の商標または登録商標です。', 'wordpressfoundation' );
				$wpf_synecoculture_text = function_exists( 'pll__' ) ? pll__( 'Synecocultureはソニーグループ株式会社の商標です。' ) : __( 'Synecocultureはソニーグループ株式会社の商標です。', 'wordpressfoundation' );
				?>
				<ul>
					<li><?php echo esc_html( $wpf_kyoseinoho_text ); ?></li>
					<li><?php echo esc_html( $wpf_synecoculture_text ); ?></li>
				</ul>
			</div>

			<div class="site-footer__copy-container">
				<?php echo do_shortcode( '[wpf_darkmode_switch]' ); ?>

				<p style="font: var(--font-text--xs)"><?php echo esc_html( $wpf_template_tags::get_copyright() ); ?></p>
			</div>
		</div>

		<a class="page-top link-muted" href="#page-top">
			<span class="screen-reader-text"><?php esc_html_e( '上へ戻る', 'wordpressfoundation' ); ?></span>
			<?php echo WPF_Icons::get_svg( 'ui', 'angle_up', 24 ); /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
		</a>
	</footer>

	<div role="dialog" aria-modal="true" aria-labelledby="searchModalTitle" id="searchModal" class="search-icon-button__modal" style="display: none">
		<span id="searchModalTitle" class="screen-reader-text"><?php echo esc_html_e( '検索', 'wordpressfoundation' ); ?></span>
		<div class="search-icon-button__modal__content">
			<?php get_search_form(); ?>
		</div>
	</div>

	<?php wp_footer(); ?>
</body>
</html>
