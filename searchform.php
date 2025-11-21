<?php
/**
 * 検索フォーム
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

?>

<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url() ); ?>">
	<div class="search-form__input__container">
		<label for="searchForm" class="screen-reader-text"><?php echo esc_html_e( '検索', 'wordpressfoundation' ); ?></label>
		<input class="search-form__input" id="searchForm" type="search" placeholder="<?php echo esc_attr( /* translators: %s: サイト名 */ sprintf( __( '%sを検索', 'wordpressfoundation' ), get_bloginfo( 'name' ) ) ); ?>" value="<?php echo get_search_query(); ?>" name="s" />
		<?php echo WPF_Icons::get_svg( 'ui', 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</div>
	<button type="submit" class="search-form__button button:secondary"><?php echo esc_attr_e( '検索', 'wordpressfoundation' ); ?></button>
</form>
