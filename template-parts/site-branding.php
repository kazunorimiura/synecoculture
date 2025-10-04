<?php
/**
 * サイトブランディング
 *
 * ページの種類に応じて適切なHTMLセマンティック、リンクを提供したり、
 * 呼び出し元に固有の変数を設定したりできる
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

$wpf_context    = $args['context'];
$wpf_site_title = get_bloginfo( 'name' );

// サイトロゴをダークモード対応するのにSVGロゴを使いたいが、WPのcustom-logoはsvgがサポートされない（セキュリティ上の理由）ためハードコードする
if ( $args['svg'] ) {
	$wpf_svg_logo = $args['svg'];
}

// サイトヘッダーのみ見出し要素とする
$wpf_tag = 'h1';
if ( 'site-header' !== $wpf_context ) {
	$wpf_tag = 'div';
}
?>

<div class="<?php echo esc_attr( $wpf_context ); ?>-branding">
	<?php

	// フロントページかつ2ページ目以降ではない場合
	if ( is_front_page() && ! is_paged() ) {
		?>
		<<?php echo esc_attr( $wpf_tag ); ?> class="<?php echo esc_attr( $wpf_context ); ?>__title">
			<?php
			if ( $wpf_svg_logo ) {
				?>
				<span class="<?php echo esc_attr( $wpf_context ); ?>__title-content">
					<span class="screen-reader-text"><?php echo esc_html( $wpf_site_title ); ?></span>
					<?php echo $wpf_svg_logo; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
				</span>
				<?php
			} else {
				?>
				<span class="<?php echo esc_attr( $wpf_context ); ?>__title-content">
					<?php
					echo esc_html( $wpf_site_title );
					?>
				</span>
				<?php
			}
			?>
		</<?php echo esc_attr( $wpf_tag ); ?>>
		<?php

		// フロントページかつ2ページ目以降、または投稿ページの場合
	} elseif ( is_front_page() || is_home() ) {
		?>
		<h1 class="<?php echo esc_attr( $wpf_context ); ?>__title">
		<?php
		if ( $wpf_svg_logo ) {
			?>
			<a 
				href="<?php echo esc_url( home_url() ); ?>"  
				class="<?php echo esc_attr( $wpf_context ); ?>__title-content"
				title="<?php esc_html_e( 'ホームページへ戻る', 'wordpressfoundation' ); ?>" 
				aria-label="<?php esc_html_e( 'ホームページへ戻る', 'wordpressfoundation' ); ?>">
				<span class="screen-reader-text"><?php echo esc_html( $wpf_site_title ); ?></span>
				<?php echo $wpf_svg_logo; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
			</a>
			<?php
		} else {
			?>
			<a 
				href="<?php echo esc_url( home_url() ); ?>" 
				class="<?php echo esc_attr( $wpf_context ); ?>__title-content">
				<?php echo esc_html( $wpf_site_title ); ?>
			</a>
			<?php
		}
		?>
		</h1>
		<?php

		// 上記以外のページの場合
	} else {
		?>
		<?php
		if ( $wpf_svg_logo ) {
			?>
			<p class="<?php echo esc_attr( $wpf_context ); ?>__title">
				<a 
					href="<?php echo esc_url( home_url() ); ?>" 
					class="<?php echo esc_attr( $wpf_context ); ?>__title-content"
					title="<?php esc_html_e( 'ホームページへ戻る', 'wordpressfoundation' ); ?>"
					aria-label="<?php esc_html_e( 'ホームページへ戻る', 'wordpressfoundation' ); ?>">
					<span class="screen-reader-text"><?php echo esc_html( $wpf_site_title ); ?></span>
					<?php echo $wpf_svg_logo; /* phpcs:ignore WordPress.Security.EscapeOutput */ ?>
				</a>
			</p>
			<?php
		} else {
			?>
			<p class="<?php echo esc_attr( $wpf_context ); ?>__title">
				<a 
					href="<?php echo esc_url( home_url() ); ?>" 
					class="<?php echo esc_attr( $wpf_context ); ?>__title-content">
					<?php echo esc_html( $wpf_site_title ); ?>
				</a>
			</p>
			<?php
		}
	}
	?>
</div>
