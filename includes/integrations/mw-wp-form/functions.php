<?php
/**
 * MW WP Formプラグインの関数。
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

$wpf_mw_wp_form_admin = new MW_WP_Form_Admin();
$wpf_forms            = $wpf_mw_wp_form_admin->get_forms();

foreach ( $wpf_forms as $wpf_form ) {
	// 改行タグの自動挿入を停止
	add_filter( 'mwform_content_wpautop_mw-wp-form-' . $wpf_form->ID, '__return_false' );

	// よく使うフォームのバリデーションメッセージを定義
	add_filter(
		'mwform_validation_mw-wp-form-' . $wpf_form->ID,
        // @codingStandardsIgnoreStart
        function ( $Validation, $data ) { 
            $Validation->set_rule( 'name', 'noempty', array( 'message' => __('氏名を入力してください。', 'wordpressfoundation') ) ); 
            $Validation->set_rule( 'last-name', 'noempty', array( 'message' => __('姓を入力してください。', 'wordpressfoundation') ) ); 
            $Validation->set_rule( 'last-name', 'between', array( 'min' => 1, 'max' => 100, 'message' => /* translators: %d: 最大文字数 */ sprintf( __( '姓は%d文字以内で入力してください。', 'wordpressfoundation' ), 100 ) ) ); 
            $Validation->set_rule( 'first-name', 'noempty', array( 'message' => __('名を入力してください。', 'wordpressfoundation') ) ); 
            $Validation->set_rule( 'first-name', 'between', array( 'min' => 1, 'max' => 100, 'message' => /* translators: %d: 最大文字数 */ sprintf( __( '名は%d文字以内で入力してください。', 'wordpressfoundation' ), 100 ) ) ); 
            // $Validation->set_rule( 'company', 'noempty', array( 'message' => __('企業・団体名を入力してください。', 'wordpressfoundation') ) ); 
            $Validation->set_rule( 'company', 'between', array( 'min' => 1, 'max' => 200, 'message' => /* translators: %d: 最大文字数 */ sprintf( __( '企業・団体名は%d文字以内で入力してください。', 'wordpressfoundation' ), 200 ) ) ); 
            $Validation->set_rule( 'email', 'noempty', array( 'message' => __('メールアドレスを入力してください。', 'wordpressfoundation') ) ); 
            $Validation->set_rule( 'email', 'mail', array( 'message' => __('メールアドレスの形式が正しくありません。', 'wordpressfoundation') ) ); 
            $Validation->set_rule( 'email', 'between', array( 'min' => 1, 'max' => 254, 'message' => /* translators: %d: 最大文字数 */ sprintf( __( 'メールアドレスは%d文字以内で入力してください。', 'wordpressfoundation' ), 254 ) ) ); 
            $Validation->set_rule( 'tel', 'noempty', array( 'message' => __('電話番号を入力してください。', 'wordpressfoundation') ) );
            $Validation->set_rule( 'tel', 'tel', array( 'message' => __('電話番号の入力形式が正しくありません。', 'wordpressfoundation') ) );
            $Validation->set_rule( 'subject', 'noempty', array( 'message' => __('件名を選択してください。', 'wordpressfoundation') ) ); 
            $Validation->set_rule( 'inquiry-type', 'required', array( 'message' => __('お問い合わせ種別を選択してください。', 'wordpressfoundation') ) ); 
            $Validation->set_rule( 'message', 'noempty', array( 'message' => __('お問い合わせ内容を入力してください。', 'wordpressfoundation') ) ); 
            $Validation->set_rule( 'message', 'between', array( 'min' => 1, 'max' => 3000, 'message' => /* translators: %d: 最大文字数 */ sprintf( __( 'お問い合わせ内容は%d文字以内で入力してください。', 'wordpressfoundation' ), 3000 ) ) ); 
            $Validation->set_rule( 'privacy-consent', 'required', array( 'message' => __('個人情報保護方針の同意が必要です。', 'wordpressfoundation') ) );
            if ( is_plugin_active( 'recaptcha-for-mw-wp-form/recaptcha-for-mw-wp-form.php' ) ) {
                $Validation->set_rule( 'recaptcha-v3', 'recaptcha_v3', array( 'message' => __('Google reCAPTCHAにより、自動化されたアクセスとして検知されました。しばらく時間をおいてから再度お試しください。', 'wordpressfoundation'), 'is_reCAPTCHA' => true ) ); 
            }
            return $Validation;
            // @codingStandardsIgnoreEnd
		},
		10,
		2
	);
}
unset( $wpf_form );

// フォームのデフォルトコンテンツを設定
if ( file_exists( get_template_directory() . '/includes/integrations/mw-wp-form/default-form-contents.php' ) ) {
	add_filter(
		'mwform_default_content',
		function ( $content ) {
			ob_start();
			get_template_part( 'includes/integrations/mw-wp-form/default-form-contents' );
			return ob_get_clean();
		},
		10,
		1
	);
}

// ユーザーへの自動返信・管理者宛メールのデフォルトコンテンツを設定
add_filter(
	'mwform_default_settings',
	function( $value, $key ) {
		$site_name   = get_bloginfo( 'name' );
		$admin_email = get_bloginfo( 'admin_email' );

		// 完了画面メッセージ
		if ( 'complete_message' === $key ) {
			return '<p>お問い合わせありがとうございます。ご入力いただいたメールアドレスに自動返信メールをお送りしました。自動返信メールが届かない場合、お手数おかけしますが、再度お試しいただくか、別の方法でお問い合わせください。</p>';
		}

		// 入力画面URL
		if ( 'input_url' === $key ) {
			return '/contact/';
		}

		// 確認画面URL
		if ( 'confirmation_url' === $key ) {
			return '/contact/confirm/';
		}

		// 完了画面URL
		if ( 'complete_url' === $key ) {
			return '/contact/thankyou/';
		}

		// エラー画面URL
		if ( 'validation_error_url' === $key ) {
			return '/contact/error/';
		}

		// 自動返信メール設定 > 件名
		if ( 'mail_subject' === $key ) {
			return '[' . $site_name . '] お問い合わせありがとうございます';
		}

		// 自動返信メール設定 > 本文
		if ( file_exists( get_template_directory() . '/includes/integrations/mw-wp-form/user-mail-content.php' ) ) {
			if ( 'mail_content' === $key ) {
				ob_start();
				get_template_part( 'includes/integrations/mw-wp-form/user-mail-content' );
				return ob_get_clean();
			}
		}

		// 自動返信メール設定 > 自動返信メール
		if ( 'automatic_reply_email' === $key ) {
			return 'email';
		}

		// 自動返信メール設定 > 送信元（E-mailアドレス）
		if ( 'mail_from' === $key ) {
			return $admin_email;
		}

		// 管理者宛メール設定 > 件名
		if ( 'admin_mail_subject' === $key ) {
			return '[' . $site_name . '] お問い合わせがありました';
		}

		// 管理者宛メール設定 > Return-Path ( メールアドレス )
		if ( 'mail_return_path' === $key ) {
			return $admin_email;
		}

		// 管理者宛メール設定 > 送信元（E-mailアドレス）
		if ( 'admin_mail_from' === $key ) {
			return $admin_email;
		}

		// 管理者宛メール > 本文
		if ( file_exists( get_template_directory() . '/includes/integrations/mw-wp-form/admin-mail-content.php' ) ) {
			if ( 'admin_mail_content' === $key ) {
				ob_start();
				get_template_part( 'includes/integrations/mw-wp-form/admin-mail-content' );
				return ob_get_clean();
			}
		}
	},
	10,
	2
);

/**
 * MW WP Formのクラシックエディタのビジュアルモードを無効にする。
 * https://gist.github.com/miurakazunori/70e0cb9bbc613a5260828c224fa66254
 *
 * @return void
 */
function wpf_disable_visual_editor_in_mw_wp_form() {
	if ( ! class_exists( 'MW_WP_Form' ) ) {
		return;
	}

	global $typenow;

	if ( 'mw-wp-form' === $typenow ) {
		add_filter( 'user_can_richedit', '__return_false', 50 );
	}
}
add_action( 'load-post.php', 'wpf_disable_visual_editor_in_mw_wp_form' );
add_action( 'load-post-new.php', 'wpf_disable_visual_editor_in_mw_wp_form' );

/**
 * エラーメッセージのHTML要素にID属性を追加する
 *
 * MW WP Formのエラーメッセージ要素に一意のID属性を追加することで、
 * aria-describedbyによるアクセシビリティ対応を可能にする。
 * エラーIDはフィールド名とバリデーションルールを組み合わせて生成される。
 *
 * @param string $error_html 元のエラーメッセージHTML
 * @param string $error エラーメッセージテキスト
 * @param string $start_tag エラー要素の開始タグ
 * @param string $end_tag エラー要素の終了タグ
 * @param string $form_key フォームキー
 * @param string $name フィールド名
 * @param string $rule バリデーションルール名
 * @return string 修正されたエラーメッセージHTML
 */
function wpf_add_id_to_error_message( $error_html, $error, $start_tag, $end_tag, $form_key, $name, $rule ) {
	// エラーIDを生成（フィールド名とルールを使用）
	$error_id = esc_attr( $name . '-' . $rule . '-error' );

	// spanタグにidを追加
	$start_tag_with_id = str_replace( '<span class="error">', '<span id="' . $error_id . '" class="error">', $start_tag );

	// 新しいHTMLを構築
	$new_error_html = $start_tag_with_id . esc_html( $error ) . $end_tag;

	return $new_error_html;
}
add_filter( 'mwform_error_message_html', 'wpf_add_id_to_error_message', 10, 7 );

/**
 * MW WP Form autocomplete属性付与
 *
 * @param string $contents 投稿コンテンツ
 * @return string
 */
function wpf_mwwpform_autocomplete( $contents ) {
	if ( ! class_exists( 'MW_WP_Form' ) ) {
		return;
	}

	if ( is_page( 'contact' ) ) {
		$form_attr = array(
			// 連絡先情報
			array( 'name', 'name' ),
			array( 'given-name', 'given-name' ),
			array( 'family-name', 'family-name' ),
			array( 'email', 'email' ),
			array( 'tel', 'tel' ),
			array( 'tel-national', 'tel-national' ),
			array( 'organization', 'organization' ),

			// 住所関連
			array( 'street-address', 'street-address' ),
			array( 'address-line1', 'address-line1' ),
			array( 'address-line2', 'address-line2' ),
			array( 'postal-code', 'postal-code' ),
			array( 'country', 'country' ),
			array( 'country-name', 'country-name' ),
			array( 'address-level1', 'address-level1' ),
			array( 'address-level2', 'address-level2' ),
			array( 'address-level3', 'address-level3' ),

			// その他
			array( 'url', 'url' ),
			array( 'sex', 'sex' ),
			array( 'bday', 'bday' ),
			array( 'language', 'language' ),
			array( 'current-password', 'current-password' ),
			array( 'new-password', 'new-password' ),

			// カスタム
			array( 'name', 'name' ),
			array( 'last-name', 'family-name' ),
			array( 'first-name', 'given-name' ),
			array( 'company', 'organization' ),
		);
		foreach ( $form_attr as $attr ) {
				$contents = str_replace( 'name="' . $attr[0] . '"', 'name="' . $attr[0] . '" autocomplete="' . $attr[1] . '"', $contents );
		}
		return $contents;
	}

	return $contents;
}
add_filter( 'the_content', 'wpf_mwwpform_autocomplete', 12 );
