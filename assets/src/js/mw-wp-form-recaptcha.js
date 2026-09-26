/**
 * MW WP Form の reCAPTCHA v3 のトークンを、送信の直前に取り直す
 *
 * reCAPTCHA for MW WP Form はページを開いた時点で一度だけトークンを取得し、フォームに入れておく。
 * トークンの有効期限は 2 分なので、入力に 2 分以上かかると期限切れのトークンが送られ、検証に失敗する。
 * 送信のたびにいったん送信を止め、取り直したトークンを入れてから送信し直す。
 *
 * 送信し直すときは、押されたボタンを引き継ぐ。MW WP Form は送られてきたボタンの name で
 * 「確認画面へ進む」「戻る」「送信する」を見分けており、ボタンが欠けると確認画面を飛ばして送信されてしまう。
 * 押されたボタンを引き継げないブラウザや、reCAPTCHA を読み込めなかったページでは手を出さず、従来どおり送信させる。
 *
 * 設定（サイトキー・戻るボタンの name）は、サーバーが window.wpfMwWpFormRecaptcha に入れて渡す。
 * 対象のブラウザはボタンを引き継げるものに限られるため、ポリフィルが要る書き方（配列のメソッド等）は避けている。
 */

// トークンを入れる隠しフィールド。name はプラグインが決めている。
const FIELD_SELECTOR = 'input[name="recaptcha-v3"]';

// トークンの取り直しを待つ上限（ミリ秒）。過ぎたら、取り直せないまま送信する。
const TIMEOUT = 10000;

/**
 * reCAPTCHA のトークンを取り直し、フォームの隠しフィールドに入れる
 *
 * 取り直せなかったとき（エラー・時間切れ）も done を呼ぶ。そのときは元のトークンのまま送信され、
 * サーバーの検証で弾かれれば、フォームにエラーメッセージが表示される。
 *
 * @param {string}          siteKey サイトキー
 * @param {HTMLFormElement} form    フォーム
 * @param {Function}        done    取り直しが終わったときに一度だけ呼ぶ関数
 */
const refreshToken = (siteKey, form, done) => {
    let finished = false;
    let timer = null;

    const finish = () => {
        if (finished) {
            return;
        }

        finished = true;
        window.clearTimeout(timer);
        done();
    };

    timer = window.setTimeout(finish, TIMEOUT);

    try {
        window.grecaptcha.ready(() => {
            try {
                return window.grecaptcha.execute(siteKey, { action: 'submit' }).then((token) => {
                    if (!finished) {
                        const fields = form.querySelectorAll(FIELD_SELECTOR);

                        for (let i = 0; i < fields.length; i++) {
                            fields[i].value = token;
                        }
                    }

                    finish();
                    return token;
                }, finish);
            } catch (error) {
                finish();
            }
        });
    } catch (error) {
        finish();
    }
};

/**
 * フォームの送信を止めてトークンを取り直し、同じボタンで送信し直す
 *
 * @param {Object}          config サーバーから渡された設定
 * @param {HTMLFormElement} form   フォーム
 */
const watch = (config, form) => {
    // トークンを取り直している最中かどうか
    let refreshing = false;

    // 取り直したトークンで送信し直している最中かどうか
    let resubmitting = false;

    form.addEventListener('submit', (event) => {
        const submitter = event.submitter;

        if (resubmitting || event.defaultPrevented) {
            return;
        }

        // 取り直している最中の送信は止める（二重送信を防ぐ）
        if (refreshing) {
            event.preventDefault();
            return;
        }

        // 押されたボタンが分からなければ、ボタンを引き継げないので手を出さない。
        // 戻るボタンで送られたときはトークンを検証しないので、取り直さない
        // （name は属性で読む。プロパティで読むと、関数名の補完コードがビルドに入ってしまう）。
        if (!submitter || submitter.getAttribute('name') === config.backButtonName) {
            return;
        }

        event.preventDefault();
        refreshing = true;

        refreshToken(config.siteKey, form, () => {
            refreshing = false;
            resubmitting = true;

            try {
                form.requestSubmit(submitter);
            } finally {
                resubmitting = false;
            }
        });
    });
};

const init = () => {
    const config = window.wpfMwWpFormRecaptcha;

    if (!config || !window.grecaptcha || typeof window.grecaptcha.ready !== 'function' || typeof HTMLFormElement.prototype.requestSubmit !== 'function') {
        return;
    }

    // 1 つのフォームを見張るのは 1 回だけにする（重なると、互いの送信し直しを止め合う）
    const forms = [];
    const fields = document.querySelectorAll(FIELD_SELECTOR);

    for (let i = 0; i < fields.length; i++) {
        const form = fields[i].form;

        if (form && forms.indexOf(form) === -1) {
            forms.push(form);
            watch(config, form);
        }
    }
};

init();
