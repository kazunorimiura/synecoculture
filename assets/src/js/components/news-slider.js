import Swiper from 'swiper';
import { Navigation } from 'swiper/modules';
import { debounce } from '../utils/debounce';

class newsSlider {
    constructor(el) {
        this.el = el;

        this.timer = null;

        this.speed = 300;

        this.swiper = new Swiper(el, {
            modules: [Navigation],
            lazy: true,
            speed: this.speed,
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
                1920: {
                    slidesPerView: 4,
                    spaceBetween: 18,
                },
            },
            navigation: {
                nextEl: '.news-slider-nav-next',
                prevEl: '.news-slider-nav-prev',
            },
        });

        this.slides = this.el.querySelectorAll('.swiper-slide');
        this.a11y();

        // 幅を記憶
        this.lastWidth = window.innerWidth;

        window.addEventListener('resize', debounce(this.onResize.bind(this), 300));
    }

    a11y() {
        // スライド内の全てのフォーカス可能な要素を取得
        this.slides.forEach((slide) => {
            const focusableElements = slide.querySelectorAll('a, button, input, select, textarea, [tabindex]:not([tabindex="-1"])');

            focusableElements.forEach((element) => {
                // フォーカスイベントを監視
                element.addEventListener('focus', () => {
                    // 要素が属するスライドを取得
                    const parentSlide = element.closest('.swiper-slide');
                    if (!parentSlide) return;

                    // スライドのインデックスを取得
                    const slideIndex = Array.from(this.slides).indexOf(parentSlide);
                    if (slideIndex === -1) return;

                    // 現在表示されているスライドの範囲を取得
                    const activeIndex = this.swiper.activeIndex;
                    const slidesPerView = this.swiper.params.slidesPerView;

                    // スライドが現在の表示範囲外の場合、スライドを移動
                    if (slideIndex < activeIndex || slideIndex >= activeIndex + slidesPerView) {
                        // slidesPerViewが数値でない場合（'auto'など）の対応
                        const targetIndex = typeof slidesPerView === 'number' ? Math.max(0, slideIndex - Math.floor((slidesPerView - 1) / 2)) : slideIndex;

                        this.swiper.slideTo(targetIndex, this.speed);
                    }
                });
            });
        });

        // ページ内のすべてのフォーカス可能な要素に対して、タブキーでの移動時にスライド表示を調整
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Tab') {
                // 少し遅延させて次の要素がフォーカスされた後に処理
                setTimeout(() => {
                    const activeElement = document.activeElement;
                    if (!activeElement) return;

                    // フォーカスされた要素がスライド内にあるか確認
                    const parentSlide = activeElement.closest('.news-slide');
                    if (!parentSlide) return;

                    // 要素が所属するSwiperを特定
                    const slideIndex = Array.from(this.slides).indexOf(parentSlide);

                    // 現在表示されているスライドの範囲を取得
                    const activeIndex = this.swiper.activeIndex;
                    const slidesPerView = this.swiper.params.slidesPerView;

                    // スライドが現在の表示範囲外の場合、スライドを移動
                    if (slideIndex < activeIndex || slideIndex >= activeIndex + slidesPerView) {
                        // slidesPerViewが数値でない場合（'auto'など）の対応
                        const targetIndex = typeof slidesPerView === 'number' ? Math.max(0, slideIndex - Math.floor((slidesPerView - 1) / 2)) : slideIndex;

                        this.swiper.slideTo(targetIndex, this.speed);
                    }
                }, 10);
            }
        });
    }

    onResize() {
        const currentWidth = window.innerWidth;

        // 幅が変わった場合のみ処理（画面回転など）
        if (currentWidth !== this.lastWidth) {
            this.swiper.update();
        }
        // 高さだけ変わった場合は無視（アドレスバー）
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
