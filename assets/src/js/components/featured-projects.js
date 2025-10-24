import { debounce } from '../utils/debounce';
import { ViewportHeightHandler } from './viewport-height-handler';

class FeaturedProjects {
    constructor() {
        this.cardEl = document.querySelector('.featured-projects__item');
        if (!this.cardEl) return;

        this.viewportHeightHandler = new ViewportHeightHandler('.featured-projects', 'below-vh', this.calcHeight());
        this.viewportHeightHandler.init();

        // 幅を記憶
        this.lastWidth = window.innerWidth;

        this.resize = this.resize.bind(this);
        window.addEventListener('resize', debounce(this.resize, 300));
    }

    calcHeight() {
        const cardHeight = this.cardEl.offsetHeight;
        console.log('cardHeight', cardHeight);
        return cardHeight;
    }

    resize() {
        const currentWidth = window.innerWidth;

        // 幅が変わった場合のみ処理（画面回転など）
        if (currentWidth !== this.lastWidth) {
            this.viewportHeightHandler.threshold = this.calcHeight();
            this.viewportHeightHandler.check();
        }
        // 高さだけ変わった場合は無視（アドレスバー）
    }
}

export function createFeaturedProjects() {
    return new FeaturedProjects();
}
