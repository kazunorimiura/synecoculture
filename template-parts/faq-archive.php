<?php
/**
 * `faq`投稿タイプのアーカイブテンプレート
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
function get_faq_contents() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'faq_cat',
			'hide_empty' => true,
		)
	);

	ob_start();
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			?>
			<h2>
				<?php echo esc_html( $term->name ); ?>
			</h2>

			<?php
			$query = new WP_Query(
				array(
					'post_type'      => 'faq',
					'posts_per_page' => -1,
					'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
							'taxonomy' => 'faq_cat',
							'field'    => 'slug',
							'terms'    => $term->slug,
						),
					),
					'orderby'        => 'date',
					'order'          => 'DESC',
				)
			);
			if ( $query->have_posts() ) {
				while ( $query->have_posts() ) {
					$query->the_post();
					?>
					<div class="accordion">
						<div class="accordion-header">
							<div class="accordion-title">
								<?php the_title(); ?>
							</div>

							<button class="accordion-button" aria-expanded="false" data-acc-target="accordion-body-<?php echo esc_attr( get_the_ID() ); ?>" data-open-text="<?php echo esc_html_e( '回答を開く', 'wordpressfoundation' ); ?>" data-close-text="<?php echo esc_html_e( '回答を閉じる', 'wordpressfoundation' ); ?>">
								<span class="screen-reader-text"><?php echo esc_html_e( '回答を開く', 'wordpressfoundation' ); ?></span>
								<?php echo WPF_Icons::get_svg( 'ui', 'angle_down', 24 ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</button>
						</div>

						<div id="accordion-body-<?php echo esc_attr( get_the_ID() ); ?>" class="accordion-body">
							<?php the_content(); ?>
						</div>
					</div>
					<?php
				}
				wp_reset_postdata();
			}
			?>
			<?php
		}
	}
	return ob_get_clean();
}
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
		$wpf_page_for_posts = WPF_Utils::get_page_for_posts();
		$wpf_show_toc       = get_post_meta( $wpf_page_for_posts, '_wpf_show_toc', true );
		if ( $wpf_show_toc ) {
			$wpf_toc      = new WPF_Toc();
			$wpf_content  = $wpf_toc->get_the_content( get_faq_contents() );
			$wpf_toc_menu = $wpf_toc->get_html_menu( $wpf_content );

			if ( $wpf_toc_menu ) {
				/**
				 * 目次（デスクトップ）
				 */
				?>
				<div class="singular__sidebar lg:hidden-yes">
					<div class="singular__sidebar__item">
						<div class="singular__sidebar__item__header">
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

		<div class="singular__main">
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

			<div class="prose">
				<?php
				/**
				 * コンテンツ
				 */
				if ( $wpf_show_toc ) {
					echo $wpf_content; // phpcs:ignore WordPress.Security.EscapeOutput
				} else {
                    echo apply_filters( 'the_content', get_faq_contents() ); // phpcs:ignore
				}
				?>
			</div>
		</div>
	</div>
</main>

<?php
get_footer();
