<?php
/**
 * ヘッダーテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

global $wpf_template_tags;

$wpf_global_nav = has_nav_menu( 'global_primary' ) || has_nav_menu( 'global_secondary' ) || has_nav_menu( 'social_links' );
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?><?php echo $wpf_template_tags::get_custom_html_attrs(); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
<head>
	<script>
		document.documentElement.classList.remove('no-js');
		document.documentElement.classList.add('js');
	</script>

	<?php
	$wpf_gtm_tag_id = get_theme_mod( 'wpf_gtm_tag_id', false );

	if ( $wpf_gtm_tag_id ) {
		?>
		<!-- Google Tag Manager -->
		<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
		new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
		j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
		'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
		})(window,document,'script','dataLayer','<?php echo esc_html( $wpf_gtm_tag_id ); ?>');</script>
		<!-- End Google Tag Manager -->
		<?php
	}
	?>

	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1" />

	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?> id="page-top">
<?php wp_body_open(); ?>
	<?php
	if ( $wpf_gtm_tag_id ) {
		?>
		<!-- Google Tag Manager (noscript) -->
		<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr( $wpf_gtm_tag_id ); ?>"
		height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
		<!-- End Google Tag Manager (noscript) -->
		<?php
	}
	?>

	<a class="skip-link screen-reader-text" href="#content">
		<?php esc_html_e( 'メインコンテンツへスキップ', 'wordpressfoundation' ); ?>
	</a>

	<header class="site-header" data-site-header>
		<div class="site-header__inner">
			<div class="site-header-branding-wrapper">
				<?php
				get_template_part( 'template-parts/site', 'logo' );
				get_template_part(
					'template-parts/site',
					'branding',
					array(
						'context' => 'site-header',
						'svg'     => wpf_get_site_logo(),
					)
				);
				?>
			</div>

			<?php
			if ( has_nav_menu( 'primary_cta' ) ) {
				?>
				<div class="primary-cta">
					<?php
					wp_nav_menu(
						array(
							'theme_location'       => 'primary_cta',
							'container'            => 'nav',
							'container_class'      => 'navigation dropdown-menu',
							'container_aria_label' => __( 'サイトアクション', 'wordpressfoundation' ),
							'menu_class'           => 'navigation__list',
							'fallback_cb'          => false,
							'walker'               => new WPF_Walker_Nav_Menu(),
							'wpf_link_classes'     => 'link-muted',
						)
					);
					?>
				</div>
				<?php
			}
			?>

			<div class="lg:hidden-yes">
				<div class="primary-nav">
					<?php
					if ( has_nav_menu( 'primary' ) ) {
						wp_nav_menu(
							array(
								'theme_location'       => 'primary',
								'container'            => 'nav',
								'container_class'      => 'navigation dropdown-menu',
								'container_aria_label' => __( 'ヘッダーナビゲーション', 'wordpressfoundation' ),
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
			</div>

			<?php
			if ( $wpf_global_nav ) {
				?>
				<div class="global-nav-button-wrapper">
					<button class="global-nav-button" aria-controls="global-nav" aria-expanded="false">
						<span class="global-nav-icon" aria-hidden="true">
							<span class="global-nav-icon__line global-nav-icon__line--top"></span> 
							<span class="global-nav-icon__line global-nav-icon__line--middle"></span>
							<span class="global-nav-icon__line global-nav-icon__line--bottom"></span>
						</span>
						<span class="screen-reader-text" data-open-text="<?php esc_attr_e( '開く', 'wordpressfoundation' ); ?>" data-close-text="<?php esc_attr_e( '閉じる', 'wordpressfoundation' ); ?>"><?php esc_html_e( '開く', 'wordpressfoundation' ); ?></span>
					</button>
				</div>

				<div class="global-nav-wrapper">
					<div class="global-nav flow ps" id="global-nav">
						<?php
						if ( has_nav_menu( 'global_primary' ) ) {
							wp_nav_menu(
								array(
									'theme_location'       => 'global_primary',
									'container'            => 'div',
									'container_class'      => 'navigation accordion-menu global-primary',
									'container_aria_label' => __( 'グローバルナビゲーション', 'wordpressfoundation' ),
									'menu_class'           => 'navigation__list',
									'fallback_cb'          => false,
									'walker'               => new WPF_Walker_Nav_Menu(),
									'wpf_link_classes'     => 'link-muted',
								)
							);
						}
						?>

						<?php
						if ( has_nav_menu( 'global_secondary' ) ) {
							wp_nav_menu(
								array(
									'theme_location'       => 'global_secondary',
									'container'            => 'div',
									'container_class'      => 'navigation accordion-menu global-secondary',
									'container_aria_label' => __( 'サブグローバルナビゲーション', 'wordpressfoundation' ),
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
									'container_class'      => 'navigation social-links global-social-links',
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
				</div>
				<?php
			}
			?>
		</div>
	</header>

	<noscript>
		<section 
			role="alert" 
			aria-live="polite" 
			class="notice:warning:subtle text-center radius-muted">
			ご使用のブラウザでJavaScriptが無効になっているため、一部機能が制限されます。
		</section>
	</noscript>

	<?php
	if ( class_exists( 'Polylang' ) ) {
		global $wpf_polylang_functions;
		$wpf_untranslated_content_notice = $wpf_polylang_functions::get_untranslated_content_notice( get_queried_object_id() );
		if ( ! empty( $wpf_untranslated_content_notice ) ) {
			?>
			<section role="alert" aria-live="polite" class="notice:warning:subtle text-center radius-muted">
				<?php echo esc_html( $wpf_untranslated_content_notice ); ?>
			</section>
			<?php
		}
	}
	?>
