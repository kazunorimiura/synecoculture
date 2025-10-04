<?php
/**
 * MW WP Formの管理者宛て自動送信メールのデフォルトコンテンツ。
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

echo '以下の内容でお問い合わせがありました。

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
このメールは ' . esc_url( home_url() ) . ' から管理者宛に自動送信されました。';
