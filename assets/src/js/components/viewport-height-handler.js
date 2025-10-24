import { debounce } from '../utils/debounce';

export class ViewportHeightHandler {
    constructor(selector, className, threshold = 500) {
        this.element = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!this.element) return;
        this.className = className;
        this.threshold = threshold;

        // 幅を記憶
        this.lastWidth = window.innerWidth;
        this.stableHeight = window.innerHeight;

        this.check = this.check.bind(this);
        this.handleResize = this.handleResize.bind(this);
        this.init();
    }

    check() {
        if (this.stableHeight <= this.threshold) {
            this.element.classList.add(this.className);
        } else {
            this.element.classList.remove(this.className);
        }
    }

    handleResize() {
        const currentWidth = window.innerWidth;

        // 幅が変わった場合のみ処理（画面回転など）
        if (currentWidth !== this.lastWidth) {
            this.lastWidth = currentWidth;
            this.stableHeight = window.innerHeight;
            this.check();
        }
        // 高さだけ変わった場合は無視（アドレスバー）
    }

    init() {
        this.check();
        window.addEventListener('resize', debounce(this.handleResize, 300));
    }

    destroy() {
        window.removeEventListener('resize', this.handleResize);
    }
}
