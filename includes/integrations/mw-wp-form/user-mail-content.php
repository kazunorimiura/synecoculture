<?php
/**
 * MW WP Formのユーザー宛て自動返信メールのデフォルトコンテンツ。
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

echo '{last_name} {first_name} 様

この度は、' . esc_html( get_bloginfo( 'name' ) ) . 'へお問い合わせいただきありがとうございます。以下の内容でお問い合わせを承りました。

姓: {last_name}
名: {first_name}
会社名: {company}
メールアドレス: {email}
電話番号: {tel}
件名: {subject}
お問い合わせ内容: 
{message}
返信を希望する: {reply}
個人情報保護方針に同意する: {privacy-consent}

--
このメールは ' . esc_url( home_url() ) . ' のお問い合わせフォームよりお問い合わせいただいた方に自動返信しています。お心当たりのない場合は、お手数ですが、このメールを破棄してくださいますよう宜しくお願いいたします。';
