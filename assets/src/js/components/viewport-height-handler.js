import { debounce } from '../utils/debounce';

export class ViewportHeightHandler {
    constructor(selector, className, threshold = 500) {
        this.element = typeof selector === 'string' ? document.querySelector(selector) : selector;
        if (!this.element) return;
        this.className = className;
        this.threshold = threshold;

        this.check = this.check.bind(this);
        this.init();
    }

    check() {
        if (window.innerHeight <= this.threshold) {
            this.element.classList.add(this.className);
        } else {
            this.element.classList.remove(this.className);
        }
    }

    init() {
        this.check();
        window.addEventListener('resize', debounce(this.check, 300));
    }

    destroy() {
        window.removeEventListener('resize', this.check);
    }
}
