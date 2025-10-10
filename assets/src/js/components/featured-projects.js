import { debounce } from '../utils/debounce';
import { ViewportHeightHandler } from './viewport-height-handler';

class FeaturedProjects {
    constructor() {
        this.cardEl = document.querySelector('.featured-projects__item');
        if (!this.cardEl) return;

        this.viewportHeightHandler = new ViewportHeightHandler('.featured-projects', 'below-vh', this.calcHeight());
        this.viewportHeightHandler.init();

        this.resize = this.resize.bind(this);
        window.addEventListener('resize', debounce(this.resize, 300));
    }

    calcHeight() {
        const cardHeight = this.cardEl.offsetHeight;
        console.log('cardHeight', cardHeight);
        return cardHeight;
    }

    resize() {
        this.viewportHeightHandler.threshold = this.calcHeight();
        this.viewportHeightHandler.check();
    }
}

export function createFeaturedProjects() {
    return new FeaturedProjects();
}
