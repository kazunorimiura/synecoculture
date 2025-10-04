import gsap, { Power2 } from 'gsap';
import { CustomEase } from 'gsap/CustomEase';
gsap.registerPlugin(CustomEase);

// gsapのカスタムイージングを定義。
export const DEFAULT_EASE = CustomEase.create('default', '0.77, 0, 0.175, 1');
export const POPUP_MENU_EASE = CustomEase.create('default', '.5,.1,0,1');

/**
 * 値と単位に分ける。
 *
 * @param {String} propValue
 * @returns
 */
export const stripUnit = (propValue) => {
    const unit = propValue
        .trim()
        .split(/\d+/g)
        .filter((n) => n)
        .pop()
        .trim();

    const value = propValue
        .trim()
        .split(unit)
        .filter((n) => n)[0]
        .trim();

    return [value, unit];
};

/**
 * タッチデバイスの判定
 *
 * 下記の回答をベースに、デスクトップChromeではwindow.ontouchstartがtrueと評価されることを考慮したロジック。
 * https://stackoverflow.com/a/4819886/20507713
 *
 * @returns {Bool}
 */
export const isTouch = () => {
    return ('ontouchstart' in window && window.ontouchstart !== null) || navigator.maxTouchPoints > 0 || navigator.msMaxTouchPoints > 0;
};

/**
 * Cookie値を取得。
 *
 * @param {String} key Cookieキー
 * @returns {String}
 */
export const getCookie = (key) => {
    const cookie = document.cookie.split('; ');

    if (!cookie) {
        return;
    }

    const keyValue = cookie.find((row) => row.startsWith(key));

    if (!keyValue) {
        return;
    }

    return keyValue.split('=')[1];
};

/**
 * スムーススクロール
 *
 * @param {URL} iURL アンカーリンクのURLインターフェイス。
 * @param {Number} [offset] スクロールの到着位置の調整値。
 * @param {Number} [duration] スクロールのアニメーション時間。
 * @returns
 */
export const anchorLink = (iURL, offset = 0, duration = 0.8, callbackFn = null) => {
    // 遷移先URLにハッシュ値がなければ返却。
    if (!iURL.hash) {
        return;
    }

    // 別ページへの遷移である場合は通常遷移。
    if (iURL.pathname !== window.location.pathname) {
        window.location.href = iURL.href;
        return;
    }

    const targetEl = document.querySelector(iURL.hash);

    // ターゲット要素が存在しない場合は返却。
    if (!targetEl) {
        return;
    }

    // 遷移先の位置を取得。
    const targetPosition = targetEl.getBoundingClientRect().top + window.pageYOffset - offset;

    // スムーズスクロールを実行。
    gsap.to(window, {
        duration: duration,
        scrollTo: { y: targetPosition, autoKill: true },
        ease: Power2.easeInOut,
        onComplete: () => {
            if (callbackFn) {
                callbackFn();
            }
        },
    });
};
