<?php
/**
 * `company-profile`固定ページのテンプレート
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

			<div class="page-main wrapper">
				<?php
				$company_profile = SCF::get( '_wpf_company_profile' );
				if ( ! empty( $company_profile ) ) {
					?>
					<div class="company-profile-container prose">
						<dl class="company-profile">
							<?php
							foreach ( $company_profile as $item ) {
								$heading = $item['_wpf_company_profile__heading'];
								$body    = $item['_wpf_company_profile__body'];
								if ( ! empty( $heading ) && ! empty( $body ) ) {
									?>
									<dt class="company-profile__item__heading">
										<?php echo esc_html( $heading ); ?>
									</dt>
									<dd class="company-profile__item">
										<?php echo wp_kses_post( $body ); ?>
									</dd>
									<?php
								}
							}
							?>
						</dl>
					</div>
					<?php
				}
				?>
			</div>
		</main>
		<?php
	}
}

get_footer();
