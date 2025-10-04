<?php
/**
 * コメント
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

/*
 * 現在の投稿がパスワードで保護されており、訪問者がまだパスワードを入力していない場合、コメントを読み込まずに返却
 */
if ( post_password_required() ) {
	return;
}

$wpf_comment_count = get_comments_number();
?>

<div id="comments" class="widget comments-area <?php echo get_option( 'show_avatars' ) ? 'show-avatars' : ''; ?>">
	<div class="flow" style="--flow-space: var(--space-s0em)">
		<?php
		comment_form(
			array(
				'logged_in_as'       => null,
				'title_reply'        => esc_html__( 'コメントする', 'wordpressfoundation' ),
				'class_container'    => 'flow comment-respond',
				'title_reply_before' => '<h2 id="reply-title" class="comment-reply-title">',
				'title_reply_after'  => '</h2>',
				'class_form'         => 'flow comment-form',
				'id_submit'          => 'comment-submit',
				'class_submit'       => 'button:secondary',
				'submit_button'      => '<button name="%1$s" type="submit" id="%2$s" class="%3$s">%4$s</button>',
			)
		);

		if ( have_comments() ) {
			?>
			<div class="comments__header">
				<span class="comments__header__count" data-comments-count="<?php echo esc_attr( $wpf_comment_count ); ?>"><?php echo esc_html( $wpf_comment_count ); ?></span>
				<span class="comments__header__count__unit"><?php esc_html_e( 'コメント', 'wordpressfoundation' ); ?></span>
			</div>

			<ol class="comment-list flow">
				<?php
				wp_list_comments(
					array(
						'avatar_size' => 48,
						'style'       => 'ol',
						'short_ping'  => true,
					)
				);
				?>
			</ol>
			<?php
			the_comments_pagination(
				array(
					'prev_text' => sprintf(
						'%s <span>%s</span>',
						WPF_Icons::get_svg( 'ui', 'angle_left' ),
						esc_html__( '前へ', 'wordpressfoundation' )
					),
					'next_text' => sprintf(
						'<span>%s</span> %s',
						esc_html__( '次へ', 'wordpressfoundation' ),
						WPF_Icons::get_svg( 'ui', 'angle_right' )
					),
				)
			);

			if ( ! comments_open() ) {
				?>
				<p class="notice no-comments"><?php esc_html_e( 'コメントは閉じています。', 'wordpressfoundation' ); ?></p>
				<?php
			}
		}
		?>
	</div>
</div>
