import Swiper from 'swiper';
import { Autoplay, EffectFade, Navigation } from 'swiper/modules';
import { debounce } from '../utils/debounce';

class synecoSlider {
    constructor(el) {
        this.el = el;

        this.timer = null;

        this.swiper = new Swiper(el, {
            modules: [Autoplay, EffectFade, Navigation],
            effect: 'fade',
            fadeEffect: { crossFade: true },
            speed: 300,
            loop: true,
            autoplay: {
                delay: 6000,
                disableOnInteraction: false,
            },
            navigation: {
                nextEl: '.syneco-slider__nav-button-next',
                prevEl: '.syneco-slider__nav-button-prev',
            },
            on: {
                slideChangeTransitionStart: function () {
                    // 全ての画像をリセット
                    const allImages = document.querySelectorAll('.syneco-slider__item-image img');
                    allImages.forEach((img) => {
                        img.style.transform = 'scale(1.15)';
                    });
                },
                slideChangeTransitionEnd: function () {
                    // アクティブなスライドの画像をアニメーション
                    const activeSlide = document.querySelector('.swiper-slide-active');
                    if (activeSlide) {
                        const activeImage = activeSlide.querySelector('.syneco-slider__item-image img');
                        if (activeImage) {
                            // すぐにスケールアニメーションを開始（6秒かけてゆっくり）
                            activeImage.style.transform = 'scale(1)';
                        }
                    }
                },
                init: function () {
                    // 初期化時に最初のスライドをアニメーション
                    setTimeout(() => {
                        const firstImage = document.querySelector('.swiper-slide-active .syneco-slider__item-image img');
                        if (firstImage) {
                            firstImage.style.transform = 'scale(1)';
                        }
                    }, 200);
                },
            },
        });

        // より滑らかなアニメーションのための追加制御
        this.isTransitioning = false;

        this.swiper.on(
            'slideChangeTransitionStart',
            function () {
                this.isTransitioning = true;

                // 全てのスライドのテキストを非表示
                const allTitles = document.querySelectorAll('.syneco-slider__item-title');
                allTitles.forEach((element) => {
                    element.style.opacity = '0';
                });
            }.bind(this),
        );

        this.swiper.on(
            'slideChangeTransitionEnd',
            function () {
                this.isTransitioning = false;

                // アクティブスライドのテキストを表示
                const activeSlide = document.querySelector('.swiper-slide-active');
                if (activeSlide) {
                    const title = activeSlide.querySelector('.syneco-slider__item-title');

                    if (title) {
                        setTimeout(() => {
                            title.style.opacity = '1';
                        }, 300);
                    }
                }
            }.bind(this),
        );

        window.addEventListener('resize', debounce(this.onResize.bind(this), 300));
    }

    onResize() {
        this.swiperResize;
    }

    swiperResize() {
        this.swiper.update();
    }
}

/**
 * synecoSliderクラスのファクトリ関数。
 *
 * @export
 * @return {synecoSlider|void} 対象要素が存在する場合、synecoSliderインスタンス。なければ何も返さない。
 */
export function createSynecoSlider() {
    const el = document.getElementById('synecoSlider');
    if (el) {
        return new synecoSlider(el);
    }
}
