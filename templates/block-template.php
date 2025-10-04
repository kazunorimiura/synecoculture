<?php
/**
 * ブロックテンプレート
 *
 * Template Name: ブロックテンプレート
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

get_header();

global $wpf_template_tags;
?>

<?php
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post();
		?>
		<main id="content">
			<div class="page-main--block-template">
				<?php
				// コンテンツ
				the_content();
				?>
			</div>
		</main>
		<?php
	}
}

get_footer();
