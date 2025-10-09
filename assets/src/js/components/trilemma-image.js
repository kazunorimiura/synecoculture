import lottie from 'lottie-web';

class LottieButtonController {
    constructor(options = {}) {
        const lang = document.documentElement.lang;
        const languageCode = lang.split('-')[0];
        const jsonFileName = lang ? `trilemma-${languageCode}.json` : `trilemma-en-US.json`;

        this.options = {
            containerSelector: '#trilemmaImage',
            buttonsSelector: '.trilemma-explain',
            buttonsContainerSelector: '.trilemma-explains',
            jsonPath: `wp-content/themes/synecoculture/assets/json/${jsonFileName}`,
            stepDuration: 3,
            totalSteps: 3,
            monitorInterval: 100,
            userClickCooldown: 1000,
            swipeThreshold: 50,
            snapDuration: 300,
            buttonOffset: 0,
            navPrevSelector: '.trilemma-image-nav__prev-button',
            navNextSelector: '.trilemma-image-nav__next-button',
            playPauseSelector: '.trilemma-play-pause-button', // 再生・停止ボタンのセレクタ
            ...options,
        };

        this.animation = null;
        this.currentStep = 1;
        this.monitorInterval = null;
        this.userClicked = false;
        this.isPlaying = true; // 再生状態を管理

        // ドラッグ関連
        this.isDragging = false;
        this.dragStartX = 0;
        this.dragCurrentX = 0;
        this.currentTranslate = 0;
        this.prevTranslate = 0;
        this.animationID = null;

        this.container = document.querySelector(this.options.containerSelector);
        if (!this.container) return;

        this.buttons = document.querySelectorAll(this.options.buttonsSelector);
        this.buttonsContainer = this.options.buttonsContainerSelector ? document.querySelector(this.options.buttonsContainerSelector) : this.buttons.length > 0 ? this.buttons[0].parentElement : null;

        // ナビゲーションボタン
        this.navPrevButton = null;
        this.navNextButton = null;
        this.playPauseButton = null; // 再生・停止ボタン

        this.init();
    }

    init() {
        if (!this.container) {
            console.error('Lottie container not found');
            return;
        }
        this.setupNavButtons();
        this.setupPlayPauseButton(); // 再生・停止ボタンのセットアップ
        this.setupEventListeners();
        this.setupDragListeners();
        this.setActiveButton(1);
        this.loadAnimation();
    }

    setupNavButtons() {
        if (this.options.navPrevSelector) {
            this.navPrevButton = document.querySelector(this.options.navPrevSelector);
        }
        if (this.options.navNextSelector) {
            this.navNextButton = document.querySelector(this.options.navNextSelector);
        }

        if (this.navPrevButton) {
            this.navPrevButton.addEventListener('click', () => this.goToPrev());
        }
        if (this.navNextButton) {
            this.navNextButton.addEventListener('click', () => this.goToNext());
        }

        this.updateNavButtonsState();
    }

    setupPlayPauseButton() {
        if (this.options.playPauseSelector) {
            this.playPauseButton = document.querySelector(this.options.playPauseSelector);
        }

        if (this.playPauseButton) {
            this.playPauseButton.addEventListener('click', () => this.togglePlayPause());

            // キーボード操作（Enter/Space）のサポート
            this.playPauseButton.addEventListener('keydown', (e) => {
                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    this.togglePlayPause();
                }
            });

            this.updatePlayPauseButton();
        }
    }

    togglePlayPause() {
        if (!this.animation) return;

        if (this.isPlaying) {
            this.pause();
        } else {
            this.play();
        }
    }

    play() {
        if (!this.animation) return;

        this.animation.play();
        this.isPlaying = true;
        this.startMonitoring();
        this.updatePlayPauseButton();
    }

    pause() {
        if (!this.animation) return;

        this.animation.pause();
        this.isPlaying = false;

        // モニタリングを停止
        if (this.monitorInterval) {
            clearInterval(this.monitorInterval);
            this.monitorInterval = null;
        }

        this.updatePlayPauseButton();
    }

    updatePlayPauseButton() {
        if (!this.playPauseButton) return;

        const playText = this.playPauseButton.dataset.playText || 'アニメーションを再生';
        const pauseText = this.playPauseButton.dataset.pauseText || 'トリレンマ図のアニメーションを一時停止';

        // アイコンとスクリーンリーダー用テキストを更新
        const srTextElement = this.playPauseButton.querySelector('.screen-reader-text');

        if (this.isPlaying) {
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

    updateNavButtonsState() {
        if (this.navPrevButton) {
            if (this.currentStep <= 1) {
                this.navPrevButton.disabled = true;
                this.navPrevButton.style.opacity = '0.3';
                this.navPrevButton.style.cursor = 'not-allowed';
            } else {
                this.navPrevButton.disabled = false;
                this.navPrevButton.style.opacity = '1';
                this.navPrevButton.style.cursor = 'pointer';
            }
        }

        if (this.navNextButton) {
            if (this.currentStep >= this.options.totalSteps) {
                this.navNextButton.disabled = true;
                this.navNextButton.style.opacity = '0.3';
                this.navNextButton.style.cursor = 'not-allowed';
            } else {
                this.navNextButton.disabled = false;
                this.navNextButton.style.opacity = '1';
                this.navNextButton.style.cursor = 'pointer';
            }
        }
    }

    goToPrev() {
        if (this.currentStep > 1) {
            this.goToStep(this.currentStep - 1);
        }
    }

    goToNext() {
        if (this.currentStep < this.options.totalSteps) {
            this.goToStep(this.currentStep + 1);
        }
    }

    setupEventListeners() {
        this.buttons.forEach((button, index) => {
            button.addEventListener('click', (e) => {
                if (this.isDragging) {
                    e.preventDefault();
                    return;
                }
                this.goToStep(index + 1);
            });
        });
    }

    setupDragListeners() {
        if (!this.buttonsContainer) return;

        this.buttonsContainer.addEventListener('mousedown', (e) => this.handleDragStart(e));
        window.addEventListener('mousemove', (e) => this.handleDragMove(e));
        window.addEventListener('mouseup', () => this.handleDragEnd());

        this.buttonsContainer.addEventListener('touchstart', (e) => this.handleDragStart(e), { passive: true });
        window.addEventListener('touchmove', (e) => this.handleDragMove(e), { passive: true });
        window.addEventListener('touchend', () => this.handleDragEnd());

        this.buttonsContainer.addEventListener('contextmenu', (e) => e.preventDefault());
    }

    handleDragStart(e) {
        this.isDragging = true;
        this.dragStartX = this.getPositionX(e);
        this.buttonsContainer.style.cursor = 'grabbing';
        this.buttonsContainer.style.transition = 'none';

        if (this.animationID) {
            cancelAnimationFrame(this.animationID);
        }
    }

    handleDragMove(e) {
        if (!this.isDragging) return;

        const currentPosition = this.getPositionX(e);
        const diff = currentPosition - this.dragStartX;
        this.currentTranslate = this.prevTranslate + diff;

        this.setTransform();
    }

    handleDragEnd() {
        if (!this.isDragging) return;

        this.isDragging = false;
        this.buttonsContainer.style.cursor = 'grab';

        const movedBy = this.currentTranslate - this.prevTranslate;

        if (Math.abs(movedBy) > this.options.swipeThreshold) {
            if (movedBy < 0 && this.currentStep < this.options.totalSteps) {
                this.goToStep(this.currentStep + 1);
            } else if (movedBy > 0 && this.currentStep > 1) {
                this.goToStep(this.currentStep - 1);
            } else {
                this.snapToActiveButton();
            }
        } else {
            this.snapToActiveButton();
        }
    }

    getPositionX(e) {
        return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
    }

    setTransform() {
        if (!this.buttonsContainer) return;
        this.buttonsContainer.style.transform = `translateX(${this.currentTranslate}px)`;
    }

    snapToActiveButton() {
        if (!this.buttonsContainer || !this.buttons[this.currentStep - 1]) return;

        const activeButton = this.buttons[this.currentStep - 1];
        const buttonRect = activeButton.getBoundingClientRect();
        const containerParentRect = this.buttonsContainer.parentElement.getBoundingClientRect();

        const targetTranslate = containerParentRect.left - buttonRect.left + this.currentTranslate + this.options.buttonOffset;

        this.animateToPosition(targetTranslate);
    }

    animateToPosition(targetTranslate) {
        const startTranslate = this.currentTranslate;
        const distance = targetTranslate - startTranslate;
        const startTime = performance.now();

        const animate = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / this.options.snapDuration, 1);

            const easeProgress = 1 - Math.pow(1 - progress, 3);

            this.currentTranslate = startTranslate + distance * easeProgress;
            this.setTransform();

            if (progress < 1) {
                this.animationID = requestAnimationFrame(animate);
            } else {
                this.prevTranslate = this.currentTranslate;
                this.animationID = null;
            }
        };

        this.animationID = requestAnimationFrame(animate);
    }

    loadAnimation() {
        this.destroy();

        this.animation = lottie.loadAnimation({
            container: this.container,
            renderer: 'svg',
            loop: true,
            autoplay: true,
            path: this.options.jsonPath,
        });

        this.animation.addEventListener('DOMLoaded', () => {
            console.log('startMonitoring', this.monitorInterval);
            this.startMonitoring();
            this.setActiveButton(1);
            this.updatePlayPauseButton();
        });
    }

    startMonitoring() {
        if (this.monitorInterval) {
            clearInterval(this.monitorInterval);
        }

        this.monitorInterval = setInterval(() => {
            this.monitorAnimation();
        }, this.options.monitorInterval);
    }

    monitorAnimation() {
        if (!this.animation) return;

        const currentFrame = this.animation.currentFrame;
        const fps = this.animation.frameRate;
        const currentTime = currentFrame / fps;

        const loopTime = this.options.stepDuration * this.options.totalSteps;
        const normalizedTime = currentTime % loopTime;
        const targetStep = Math.floor(normalizedTime / this.options.stepDuration) + 1;

        if (!this.userClicked && targetStep !== this.currentStep && targetStep <= this.options.totalSteps) {
            this.setActiveButton(targetStep);
        }
    }

    setActiveButton(step) {
        this.buttons.forEach((btn) => btn.classList.remove('active'));

        if (this.buttons[step - 1]) {
            this.buttons[step - 1].classList.add('active');
            this.currentStep = step;

            this.updateNavButtonsState();

            if (!this.isDragging) {
                this.snapToActiveButton();
            }
        }
    }

    goToStep(step) {
        if (!this.animation || step < 1 || step > this.options.totalSteps) return;

        this.userClicked = true;
        this.setActiveButton(step);

        const targetTime = (step - 1) * this.options.stepDuration;
        const targetFrame = targetTime * this.animation.frameRate;

        this.animation.goToAndPlay(targetFrame, true);

        setTimeout(() => {
            this.userClicked = false;
        }, this.options.userClickCooldown);
    }

    destroy() {
        if (this.animation) {
            this.animation.destroy();
            this.animation = null;
        }

        if (this.monitorInterval) {
            clearInterval(this.monitorInterval);
            this.monitorInterval = null;
        }

        if (this.animationID) {
            cancelAnimationFrame(this.animationID);
            this.animationID = null;
        }
    }
}

export function createTrilemmaImage() {
    return new LottieButtonController();
}
