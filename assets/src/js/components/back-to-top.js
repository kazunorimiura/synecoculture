export class BackToTop {
    constructor(el) {
        this.el = typeof el === 'string' ? document.querySelector(el) : el;
        if (!this.el) {
            return;
        }

        this.offset = 120;
        this.last_known_scroll_position = 0;
        this.ticking = false;

        ['handleScroll'].forEach((method) => {
            this[method] = this[method].bind(this);
        });
        this.attachEvents();

        this.init();
    }

    init() {
        this.last_known_scroll_position = window.scrollY;

        if (this.last_known_scroll_position > this.offset) {
            this.el.classList.add('is-show');
        } else {
            this.el.classList.remove('is-show');
        }
    }

    /**
     * リスナーを必要なイベントに接続
     */
    attachEvents() {
        window.addEventListener('scroll', this.handleScroll);
    }

    /**
     * イベントをデタッチ
     */
    detachEvents() {
        window.removeEventListener('scroll', this.handleScroll);
    }

    /**
     * スクロールイベントをハンドリング
     */
    handleScroll() {
        this.last_known_scroll_position = window.scrollY;

        if (!this.ticking) {
            window.requestAnimationFrame(() => {
                if (this.last_known_scroll_position > this.offset) {
                    this.el.classList.add('is-show');
                } else {
                    this.el.classList.remove('is-show');
                }
                this.ticking = false;
            });
            this.ticking = true;
        }
    }
}
