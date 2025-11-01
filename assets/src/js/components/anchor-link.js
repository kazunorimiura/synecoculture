import gsap, { ScrollToPlugin, Power2 } from 'gsap/all';
gsap.registerPlugin(ScrollToPlugin);

export class AnchorLink {
    constructor(options) {
        this.config = AnchorLink.mergeSettings(options);
        this.isScrolling = false; // スクロール中フラグを追加

        this.aEl = !this.config.a.selector ? document.querySelector(this.config.a.selector) : this.config.a.el;

        if (!this.aEl) {
            return;
        }

        if (!this.aEl.href) {
            return;
        }

        this.iURL = new URL(this.aEl.href);

        this.onClick = this.handleClick.bind(this);
        this.onTouchMove = this.handleTouchMove.bind(this); // 追加

        this.attachEvents();
    }

    static mergeSettings(options) {
        const settings = {
            a: {
                selector: 'a',
                el: null,
            },
            callbackAfter: null,
            callbackBefore: null,
            offset: 0,
            duration: 0.8,
        };

        const userSttings = options;
        for (const attrname in userSttings) {
            settings[attrname] = userSttings[attrname];
        }

        return settings;
    }

    attachEvents() {
        this.aEl.addEventListener('click', this.onClick);
    }

    // スクロール中のタッチ移動を防止
    handleTouchMove(e) {
        if (this.isScrolling) {
            e.preventDefault();
        }
    }

    updateOffset(value) {
        this.config.offset = value;
    }

    handleClick(e) {
        e.preventDefault();

        this.smoothScroll();
    }

    smoothScroll() {
        // 別ページへの遷移である場合は通常遷移。
        if (this.iURL.pathname !== window.location.pathname) {
            window.location.href = this.iURL.href;
            return;
        }

        // 遷移先URLにハッシュ値がなければ返却。
        if (!this.iURL.hash) {
            return;
        }

        // セレクタ文字列にコロンを含む場合はエスケープ処理を施す。
        const selector = this.iURL.hash.replace(/:/, '\\:');

        const targetEl = document.querySelector(selector);

        // ターゲット要素が存在しない場合は返却。
        if (!targetEl) {
            return;
        }

        // URL履歴を更新する。
        if (history.pushState) {
            history.pushState(null, null, this.iURL);
        }

        // 遷移先の位置を取得。
        const targetPosition = targetEl.getBoundingClientRect().top + window.scrollY - this.config.offset;

        // スクロール中のタッチイベントを制御
        this.isScrolling = true;
        document.addEventListener('touchmove', this.onTouchMove, { passive: false });

        // スムーズスクロールを実行。
        gsap.to(window, {
            duration: this.config.duration,
            scrollTo: { y: targetPosition, autoKill: false },
            ease: Power2.easeInOut,
            onStart: () => {
                if (this.config.callbackBefore) {
                    this.config.callbackBefore();
                }
            },
            onComplete: () => {
                // スクロール完了後、タッチイベントリスナーを削除
                this.isScrolling = false;
                document.removeEventListener('touchmove', this.onTouchMove);

                if (this.config.callbackAfter) {
                    this.config.callbackAfter();
                }

                // 遷移先の最初のフォーカス可能な要素を取得
                const focusableElements = targetEl.querySelectorAll('a[href], button, input, textarea, select, details, [tabindex]:not([tabindex="-1"])');

                // フォーカス可能な要素があれば最初の要素にフォーカスを当てる
                if (focusableElements.length > 0) {
                    focusableElements[0].focus();
                } else {
                    // フォーカス可能な要素がない場合は、遷移先の要素自体にフォーカスを当てる
                    // tabindex属性を一時的に追加してフォーカス可能にする
                    targetEl.setAttribute('tabindex', '-1');
                    targetEl.focus();
                }
            },
            onInterrupt: () => {
                // アニメーションが中断された場合もクリーンアップ
                this.isScrolling = false;
                document.removeEventListener('touchmove', this.onTouchMove);
            },
        });
    }
}
