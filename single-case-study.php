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

/**
 * 事例コンテンツ
 *
 * @return string
 */
function get_case_study_content() {
	ob_start();
	?>
	<?php
	$basic = SCF::get( '_wpf_case_study__basic' );
	if ( ! WPF_Utils::is_array_empty( $basic ) ) {
		?>
		<h2>
			<?php echo esc_html_e( '基本情報', 'wordpressfoundation' ); ?>
		</h2>

		<dl>
			<?php
			foreach ( $basic as $item ) {
				$heading = $item['_wpf_case_study__basic__heading'];
				$body    = $item['_wpf_case_study__basic__body'];
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
	$detail = SCF::get( '_wpf_case_study__detail' );
	if ( ! WPF_Utils::is_array_empty( $detail ) ) {
		?>
		<h2>
			<?php echo esc_html_e( '実践内容', 'wordpressfoundation' ); ?>
		</h2>

		<dl>
			<?php
			foreach ( $detail as $item ) {
				$heading = $item['_wpf_case_study__detail__heading'];
				$body    = $item['_wpf_case_study__detail__body'];
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
	$results = SCF::get( '_wpf_case_study__results' );
	if ( ! WPF_Utils::is_array_empty( $results ) ) {
		?>
		<h2>
			<?php echo esc_html_e( '成果・気づき', 'wordpressfoundation' ); ?>
		</h2>

		<dl>
			<?php
			foreach ( $results as $item ) {
				$heading = $item['_wpf_case_study__results__heading'];
				$body    = $item['_wpf_case_study__results__body'];
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
	$log = SCF::get( '_wpf_case_study__log' );
	if ( ! WPF_Utils::is_array_empty( $log ) ) {
		?>
		<h2>
			<?php echo esc_html_e( '観察記録', 'wordpressfoundation' ); ?>
		</h2>

		<div class="observe-log-container">
			<?php
			foreach ( $log as $item ) {
				$date     = $item['_wpf_case_study__log__date'];
				$image_id = $item['_wpf_top__learn__image'];
				$body     = $item['_wpf_case_study__log__body'];
				if ( ! empty( $date ) && ! empty( $body ) ) {
					?>
					<div class="observe-log">
						<div class="observe-log__date">
							<?php echo esc_html( $date ); ?>
						</div>

						<div class="observe-log__main">
							<?php
							$image = wp_get_attachment_image(
								$image_id,
								'large',
								false,
								array(
									'loading' => 'lazy',
								)
							);
							if ( ! empty( $image ) ) {
								?>
								<div class="observe-log__image">
									<?php echo $image; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</div>
								<?php
							}
							?>

							<div class="observe-log__body">
								<?php echo wp_kses_post( $body ); ?>
							</div>
						</div>
					</div>
					<?php
				}
			}
			?>
		</dl>
		<?php
	}
	return ob_get_clean();
}
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
					$wpf_content  = $wpf_toc->get_the_content( apply_filters( 'the_content', get_the_content() . get_case_study_content() ) );
					$wpf_toc_menu = $wpf_toc->get_html_menu( $wpf_content );

					if ( $wpf_toc_menu ) {
						/**
						 * 目次（デスクトップ）
						 */
						?>
						<div class="single-case-study__sidebar lg:hidden-yes">
							<div class="single-case-study__sidebar__item">
								<div class="single-case-study__sidebar__item__header">
									<div class="syneco-overline">
										<div class="syneco-overline__icon">
											<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										</div>
										<div class="syneco-overline__text">In this article</div>
									</div>
								</div>

								<nav class="singular__toc toc flow over-scroll" aria-label="In this article">
									<?php echo $wpf_toc_menu; // phpcs:ignore WordPress.Security.EscapeOutput ?>
								</nav>
							</div>
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
								<details class="toc flow" aria-label="In this article">
									<summary>
										<div class="syneco-overline">
											<div class="syneco-overline__icon">
												<?php echo WPF_Icons::get_svg( 'ui', 'syneco', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
											</div>
											<div class="syneco-overline__text">In this article</div>
										</div>
									</summary>

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
			                echo apply_filters( 'the_content', get_the_content() . get_case_study_content() ); // phpcs:ignore
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
