<?php
/**
 * MW WP Formのデフォルトコンテンツ
 *
 * @package wordpressfoundation
 * @since 0.1.0
 */

echo '<div class="prose">
    [mwform_akismet_error]
    [mwform_error keys="recaptcha-v3"]

    <div class="grid" style="--grid-column-width: calc(50% - var(--space-s0)); --grid-gap: var(--space-s0)">
        <p>
            <label for="last-name" class="form-label">姓</label>
            [mwform_text name="last-name" id="last-name" size="60"]
        </p>
        <p>
            <label for="first-name" class="form-label">名</label>
            [mwform_text name="first-name" id="first-name" size="60"]
        </p>
    </div>

    <p>
        <label for="company" class="form-label">会社名</label>
        [mwform_text name="company" id="company" size="60"]
    </p>

    <p>
        <label for="email" class="form-label">メールアドレス</label>
        [mwform_email name="email" id="email" size="60"]
    </p>

    <p>
        <label for="tel" class="form-label">電話番号</label>
        [mwform_text name="tel" id="tel" size="60"]
    </p>

    <p>
        <label for="subject" class="form-label">件名</label>
        [mwform_select name="subject" id="subject" children=":選択してください,製品に関するお問い合わせ,取材に関するお問い合わせ,その他のお問い合わせ" post_raw="true"]
    </p>

    <p>
        <label for="message" class="form-label">お問い合わせ内容</label>
        [mwform_textarea name="message" id="message" cols="60" rows="8"]
    </p>

    <p>
        <span class="form-label">返信を希望する</span>
        <span class="d-block mbs-s-12">
            [mwform_radio name="reply" id="reply" children="yes:はい,no:いいえ" value="yes" show_error="false"]
        </span>
        <span class="d-block font-text--xs">※必ずしも返信を保証するものではありません。</span>
    </p>

    <p class="text-center" style="--flow-space: var(--space-s3)">
        <span class="d-block mbe-s-3 font-text--sm">取得した個人情報は<a href="/privacy-policy/" target="_blank" rel="noopener">個人情報保護方針</a>に従って取り扱いを行います。</span>
        [mwform_checkbox name="privacy-consent" id="privacy-consent" children="個人情報保護方針に同意する" separator=","]
    </p>

    <p>
        <span class="d-flex gap-s-3 jc-center ai-center mb-s4 text-center">
            [mwform_bconfirm class="button:primary:wide" value="confirm"]確認画面へ[/mwform_bconfirm]
            [mwform_bback class="button:tertiary:wide" value="back"]戻る[/mwform_bback]
            [mwform_bsubmit name="submit" class="button:primary:wide" value="send"]送信する[/mwform_bsubmit]
        </span>
    </p>

    <p class="font-text--xs text-center">このフォームはreCAPTCHAによって保護されており、Googleの<a href="https://policies.google.com/privacy" target="_blank" rel="noopener">プライバシーポリシー</a>と<a href="https://policies.google.com/terms" target="_blank" rel="noopener">利用規約</a>が適用されます。</p>

    [mwform_hidden name="recaptcha-v3"]
</div>';
