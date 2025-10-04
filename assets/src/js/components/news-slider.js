import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';
import { debounce } from '../utils/debounce';

class newsSlider {
    constructor(el) {
        this.el = el;

        this.timer = null;

        this.swiper = new Swiper(el, {
            modules: [Navigation],
            lazy: true,
            slidesPerView: 1,
            spaceBetween: 32,
            breakpoints: {
                768: {
                    slidesPerView: 2,
                    spaceBetween: 24,
                },
                1024: {
                    slidesPerView: 3,
                    spaceBetween: 20,
                },
            },
            navigation: {
                nextEl: '.news-slider-nav-next',
                prevEl: '.news-slider-nav-prev',
            },
        });

        window.addEventListener('resize', debounce(this.onResize.bind(this), 300));
    }

    onResize() {
        this.swiper.update();
    }
}

/**
 * newsSliderクラスのファクトリ関数。
 *
 * @export
 * @return {newsSlider|void} 対象要素が存在する場合、newsSliderインスタンス。なければ何も返さない。
 */
export function createNewsSlider() {
    const el = document.getElementById('newsSlider');
    if (el) {
        return new newsSlider(el);
    }
}
