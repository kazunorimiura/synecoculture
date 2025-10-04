<?php
/**
 * ページヘッダー
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

$wpf_subtitle = isset( $args['subtitle'] ) ? $args['subtitle'] : '';
?>

<div class="page-header">
	<?php
	$wpf_breadcrumbs = WPF_Template_Tags::get_the_breadcrumbs();
	if ( $wpf_breadcrumbs ) {
		?>
		<div class="bg-color-background-primary border-bottom">
			<div class="wrapper:stretch pb-s-3 over-scroll">
				<nav class="breadcrumbs" aria-label="<?php esc_html_e( 'パンくずリスト', 'wordpressfoundation' ); ?>">
					<ul class="breadcrumbs__list">
				<?php
				foreach ( $wpf_breadcrumbs as $wpf_breadcrumb ) {
					?>
						<li>
						<?php
						if ( end( $wpf_breadcrumbs ) === $wpf_breadcrumb ) {
							echo $wpf_breadcrumb['text']; // phpcs:ignore WordPress.Security.EscapeOutput
						} else {
							?>
							<a href="<?php echo esc_url( $wpf_breadcrumb['link'] ); ?>">
								<?php echo $wpf_breadcrumb['text']; // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</a>
							<?php
						}
						?>
						</li>
						<?php
				}
				?>
					</ul>
				</nav>
			</div>
		</div>
		<?php
	}
	?>

	<?php
	// 著者ページの場合
	if ( is_author() ) {
		$wpf_user_id          = (int) get_query_var( 'author' );
		$wpf_avatar           = get_avatar( $wpf_user_id, 300 );
		$wpf_display_name     = function_exists( 'pll__' ) ? pll__( get_the_author_meta( 'display_name', $wpf_user_id ), 'wordpressfoundation' ) : get_the_author_meta( 'display_name', $wpf_user_id );
		$wpf_position         = function_exists( 'pll__' ) ? pll__( get_the_author_meta( 'position', $wpf_user_id ), 'wordpressfoundation' ) : get_the_author_meta( 'position', $wpf_user_id );
		$wpf_description      = get_the_author_meta( 'description', $wpf_user_id );
		$wpf_social_links     = array(
			'x'         => get_the_author_meta( 'x', $wpf_user_id ),
			'instagram' => get_the_author_meta( 'instagram', $wpf_user_id ),
			'facebook'  => get_the_author_meta( 'facebook', $wpf_user_id ),
		);
		$wpf_social_icon_size = 36;
		?>
		<div class="wrapper:wide border-bottom">
			<div class="author-info">
				<?php
				// アバター
				if ( ! empty( $wpf_avatar ) ) {
					?>
					<a 
						class="avatar:lg" 
						href="<?php echo esc_url( get_author_posts_url( $wpf_user_id ) ); ?>" 
						title="<?php echo esc_attr( /* translators: 著者名 */ sprintf( __( '%sのプロフィールを見る', 'wordpressfoundation' ), $wpf_display_name ) ); ?>"
						aria-label="<?php echo esc_attr( /* translators: 著者名 */ sprintf( __( '%sのプロフィールを見る', 'wordpressfoundation' ), $wpf_display_name ) ); ?>">
						<?php echo $wpf_avatar; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
					</a>
					<?php
				}
				?>

				<div>
					<?php
					if ( ! empty( $wpf_display_name ) ) {
						?>
						<a href="<?php echo esc_url( get_author_posts_url( $wpf_user_id ) ); ?>">
							<?php echo esc_html( $wpf_display_name ); ?>
						</a>
						<?php
					}

					if ( ! empty( $wpf_position ) ) {
						?>
						<p>
							<?php echo esc_html( $wpf_position ); ?>
						</p>
						<?php
					}

					if ( ! empty( $wpf_description ) ) {
						?>
						<p>
							<?php echo esc_html( $wpf_description ); ?>
						</p>
						<?php
					}

					if ( count( array_filter( $wpf_social_links ) ) !== 0 ) {
						?>
						<div>
							<?php
							foreach ( $wpf_social_links as $wpf_key => $wpf_value ) {
								if ( ! empty( $wpf_value ) ) {
									?>
									<a href="<?php echo esc_url( $wpf_value ); ?>" target="_brank">
										<?php echo WPF_Icons::get_svg( 'social', $wpf_key, $wpf_social_icon_size ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
										<span class="screen-reader-text"><?php echo esc_html( ucfirst( $wpf_key ) ); ?></span>
									</a>
									<?php
								}
							}
							?>
						</div>
						<?php
					}
					?>
				</div>
			</div>
		</div>
		<?php

		// 検索結果ページの場合
	} elseif ( is_search() ) {
		?>
		<div class="wrapper:wide border-bottom">
			<div class="flow">
				<h1>
					<?php
					esc_html_e( 'Search', 'wordpressfoundation' );
					?>
				</h1>

				<?php get_search_form(); ?>
			</div>
		</div>
		<?php

		// 上記以外のページの場合
	} else {
		$wpf_title = WPF_Template_Tags::get_the_page_title();
		if ( ! empty( $wpf_title ) ) {
			?>
			<div class="wrapper:wide border-bottom">
				<div class="page-header__content">
					<div class="region" style="--region-space: var(--space-s6)">
						<div class="flow" style="--flow-space: var(--space-s-1em)">
							<?php
							if ( is_single() ) {
								?>
								<div class="cluster mbe-s-3">
									<?php
									$wpf_terms = WPF_Utils::get_the_terms();
									if ( ! empty( $wpf_terms ) && 'uncategorized' !== $wpf_terms[0]->slug ) {
										?>
										<p 
											class="c-content-positive font-text--xs"
											style="--flow-space: var(--space-s-space)">
											<a 
												href="<?php echo esc_url( get_term_link( $wpf_terms[0]->term_id, $wpf_terms[0]->taxonomy ) ); ?>"
												class="pill">
												<?php echo esc_html( $wpf_terms[0]->name ); ?>
											</a>
										</p>
										<?php
									}
									?>

									<div class="page-header__meta cluster" style="--cluster-space: 0 0.75em">
										<div class="cluster">
											<div>
												<?php
												echo WPF_Template_Tags::get_the_publish_date_tag(); // phpcs:ignore WordPress.Security.EscapeOutput
												?>
											</div>
										</div>
									</div>
								</div>
								<?php
							}
							?>

							<h1>
								<?php echo wp_kses_post( $wpf_title ); ?>
							</h1>

							<?php
							if ( $wpf_subtitle ) {
								?>
								<p class="page-header__subtitle"><?php echo wp_kses_post( $wpf_subtitle ); ?></p>
								<?php
							}
							?>
						</div>
					</div>
				</div>
			</div>
			<?php
		}
	}
	?>
</div>
