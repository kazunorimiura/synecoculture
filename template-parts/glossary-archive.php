<?php
/**
 * `glossary`投稿タイプのアーカイブテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

// phpcs:disable WordPress.NamingConventions.PrefixAllGlobals,WordPress.WP.GlobalVariablesOverride

get_header();

global $wpf_template_tags;

$current_lang = pll_current_language();

// 言語ごとのカテゴリー順序を定義
$category_order = array(
	'ja' => array( 'あ', 'か', 'さ', 'た', 'な', 'は', 'ま', 'や', 'ら', 'わ', 'ん' ),
	'en' => array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' ),
	'zh' => array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'W', 'X', 'Y', 'Z' ),
	'fr' => array( 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z' ),
);

$categories = $category_order[ $current_lang ] ? $category_order[ $current_lang ] : $category_order['en'];

// 各カテゴリーの投稿数を事前に取得
$category_counts = array();
foreach ( $categories as $cat ) {
	$term = get_term_by( 'slug', $cat, 'glossary_cat' );
	if ( $term ) {
		$args                    = array(
			'post_type'      => 'glossary',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
				array(
					'taxonomy' => 'glossary_cat',
					'field'    => 'slug',
					'terms'    => $cat,
				),
			),
			'lang'           => $current_lang,
		);
		$query                   = new WP_Query( $args );
		$category_counts[ $cat ] = $query->found_posts;
		wp_reset_postdata();
	} else {
		$category_counts[ $cat ] = 0;
	}
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

	<div class="archive-glossary-nav-container wrapper:stretch">
		<nav class="archive-glossary-nav">
			<ul>
				<?php
				foreach ( $categories as $cat ) {
					$has_posts = $category_counts[ $cat ] > 0;
					?>
					<li>
						<?php
						if ( $has_posts ) {
							// マルチバイト文字列の場合、文字列のハッシュベースIDを生成（同じ文字列には常に同じIDを返す）
							$anchor_id = strlen( $cat ) !== mb_strlen( $cat, 'UTF-8' ) ? '#cat-' . substr( md5( $cat ), 0, 12 ) : '#cat-' . $cat;
							?>
							<a href="<?php echo esc_attr( $anchor_id ); ?>">
								<?php echo esc_html( $cat ); ?>
							</a>
							<?php
						} else {
							?>
							<span class="disabled">
								<?php echo esc_html( $cat ); ?>
							</span>
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

	<div class="page-main wrapper:stretch">
		<div class="archive-glossary">
			<?php
			foreach ( $categories as $cat ) {
				if ( 0 === $category_counts[ $cat ] ) {
					continue; // 投稿がなければスキップ
				}

				// カテゴリーに紐づく投稿を取得
				$args = array(
					'post_type'      => 'glossary',
					'posts_per_page' => -1,
					'orderby'        => 'title',
					'order'          => 'ASC',
					'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_tax_query
						array(
							'taxonomy' => 'glossary_cat',
							'field'    => 'slug',
							'terms'    => $cat,
						),
					),
					'lang'           => $current_lang,
				);

				$query = new WP_Query( $args );

				if ( $query->have_posts() ) {
					// マルチバイト文字列の場合、文字列のハッシュベースIDを生成（同じ文字列には常に同じIDを返す）
					$anchor_id = strlen( $cat ) !== mb_strlen( $cat, 'UTF-8' ) ? 'cat-' . substr( md5( $cat ), 0, 12 ) : 'cat-' . $cat;
					?>
					<div class="archive-glossary__cat-section">
						<h2 id="<?php echo esc_attr( $anchor_id ); ?>" class="archive-glossary__items__heading">
							<?php echo esc_html( $cat ); ?>
						</h2>

						<div class="archive-glossary__items">
							<?php
							while ( $query->have_posts() ) {
								$query->the_post();
								?>
								<article class="archive-glossary__item">
									<?php
									$image_url = '';
									$image     = wp_get_attachment_image_src( get_post_thumbnail_id(), 'rich_link' );
									if ( $image ) {
										$image_url = $image[0];
									}
									$wpf_rich_link = do_shortcode( '[wpf_rich_link url="' . get_the_permalink() . '" text="' . get_the_title() . '" image_url="' . $image_url . '" no_image="false"]' );
									if ( ! empty( $wpf_rich_link ) ) {
										echo $wpf_rich_link; // phpcs:ignore WordPress.Security.EscapeOutput
									}
									?>
								</article>
								<?php
							}
							?>
						</div>
					</div>
					<?php
				}
				wp_reset_postdata();
			}
			?>
		</div>
	</div>
</main>

<?php
get_footer();
