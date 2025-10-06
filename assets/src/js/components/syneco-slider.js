import Swiper from 'swiper';
import { Autoplay, EffectFade, Navigation } from 'swiper/modules';
import { debounce } from '../utils/debounce';

class synecoSlider {
    constructor(el) {
        this.el = el;

        this.timer = null;

        this.speed = 300;

        this.playPauseButton = document.querySelector('.syneco-slider__play-pause-button');

        this.swiper = new Swiper(el, {
            modules: [Autoplay, EffectFade, Navigation],
            effect: 'fade',
            fadeEffect: { crossFade: true },
            speed: this.speed,
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
                    const firstImage = document.querySelector('.swiper-slide-active .syneco-slider__item-image img');
                    if (firstImage) {
                        firstImage.style.transform = 'scale(1)';
                    }
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
                        title.style.opacity = '1';
                    }
                }
            }.bind(this),
        );

        this.slides = this.el.querySelectorAll('.swiper-slide');
        this.a11y();

        this.setupPlayPauseButton(); // 再生・停止ボタンのセットアップ

        window.addEventListener('resize', debounce(this.onResize.bind(this), 300));
    }

    setupPlayPauseButton() {
        if (this.playPauseButton) {
            this.playPauseButton.addEventListener(
                'click',
                function () {
                    this.togglePlayPause();
                }.bind(this),
            );

            // キーボード操作（Enter/Space）のサポート
            this.playPauseButton.addEventListener(
                'keydown',
                function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.togglePlayPause();
                    }
                }.bind(this),
            );

            this.updatePlayPauseButton();
        }
    }

    togglePlayPause() {
        if (!this.swiper.autoplay.paused) {
            this.pause();
        } else {
            this.play();
        }
    }

    play() {
        // Swiperの自動スライドを再開
        this.swiper.autoplay.resume();

        // アクティブなスライドの画像を取得
        const activeSlide = document.querySelector('.swiper-slide-active');
        if (activeSlide) {
            const activeImage = activeSlide.querySelector('.syneco-slider__item-image img');
            if (activeImage) {
                activeImage.style.transition = 'transform 6s ease-out';
                activeImage.style.transform = 'scale(1)';
            }
        }

        this.updatePlayPauseButton();
    }

    pause() {
        // Swiperの自動スライドを停止
        this.swiper.autoplay.pause();

        // アクティブなスライドの画像を取得
        const activeSlide = document.querySelector('.swiper-slide-active');
        if (activeSlide) {
            const activeImage = activeSlide.querySelector('.syneco-slider__item-image img');
            if (activeImage) {
                activeImage.style.transform = getComputedStyle(activeImage).transform;
                activeImage.style.transition = 'none';
            }
        }

        this.updatePlayPauseButton();
    }

    updatePlayPauseButton() {
        if (!this.playPauseButton) return;

        const playText = this.playPauseButton.dataset.playText || 'ヒーロースライダーのアニメーションを再生';
        const pauseText = this.playPauseButton.dataset.pauseText || 'ヒーロースライダーのアニメーションを一時停止';

        // アイコンとスクリーンリーダー用テキストを更新
        const srTextElement = this.playPauseButton.querySelector('.screen-reader-text');

        if (!this.swiper.autoplay.paused) {
            // 再生中 → 停止アイコンを表示
            this.playPauseButton.classList.remove('is-paused');
            this.playPauseButton.classList.add('is-playing');

            if (srTextElement) {
                srTextElement.textContent = pauseText;
            }
        } else {
            // 停止中 → 再生アイコンを表示
            this.playPauseButton.classList.remove('is-playing');
            this.playPauseButton.classList.add('is-paused');

            if (srTextElement) {
                srTextElement.textContent = playText;
            }
        }
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
                    const parentSlide = activeElement.closest('.syneco-slider__item');
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
