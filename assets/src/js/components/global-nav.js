import gsap from 'gsap';

import { DEFAULT_EASE, stripUnit } from '../utilities';

class GlobalNavIcon {
    constructor(globalNav, options) {
        this.globalNav = globalNav;
        this.config = GlobalNavIcon.mergeSettings(options);
        this.globalNavIconEl = !this.config.globalNavIcon.el ? this.globalNav.triggerEl.querySelector(this.config.globalNavIcon.selector) : this.config.globalNavIcon.el;
        this.globalNavIconTopLineEl = !this.config.globalNavIconTopLine.el ? this.globalNav.triggerEl.querySelector(this.config.globalNavIconTopLine.selector) : this.config.globalNavIconTopLine.el;
        this.globalNavIconMiddleLineEl = !this.config.globalNavIconMiddleLine.el ? this.globalNav.triggerEl.querySelector(this.config.globalNavIconMiddleLine.selector) : this.config.globalNavIconMiddleLine.el;
        this.globalNavIconBottomLineEl = !this.config.globalNavIconBottomLine.el ? this.globalNav.triggerEl.querySelector(this.config.globalNavIconBottomLine.selector) : this.config.globalNavIconBottomLine.el;

        if (!this.globalNavIconEl || !this.globalNavIconTopLineEl || !this.globalNavIconBottomLineEl) {
            throw new Error('globalNav icon not found.');
        }

        this.calcIconDims();

        this.timeline = gsap.timeline({
            defaults: { duration: 0.3 },
            paused: true,
            reversed: true,
            onStart: () => {
                this.onAnimateStart();
            },
            onComplete: () => {
                this.onAnimateEnd();
            },
            onReverseComplete: () => {
                this.onAnimateEnd();
            },
        });

        this.timeline
            .to(
                this.globalNavIconTopLineEl,
                {
                    scaleX: this.globalNavIconWidthValue > 32 ? 32 / this.globalNavIconWidthValue : 1,
                    translateY: `${this.globalNavIconHeightValue / 2 - this.globalNavIconLineWeightValue / 2}${this.globalNavIconHeightUnit}`,
                    ease: 'expo.in',
                },
                'press',
            )
            .to(
                this.globalNavIconBottomLineEl,
                {
                    scaleX: this.globalNavIconWidthValue > 32 ? 32 / this.globalNavIconWidthValue : 1,
                    translateY: `-${this.globalNavIconHeightValue / 2 - this.globalNavIconLineWeightValue / 2}${this.globalNavIconHeightUnit}`,
                    ease: 'expo.in',
                },
                'press',
            );

        if (this.globalNavIconMiddleLineEl) {
            this.timeline.to(this.globalNavIconMiddleLineEl, {
                opacity: '0',
                duration: 0,
            });
        }

        this.timeline
            .to(
                this.globalNavIconTopLineEl,
                {
                    rotate: '45deg',
                    ease: 'expo.out',
                },
                'rotate',
            )
            .to(
                this.globalNavIconBottomLineEl,
                {
                    rotate: '-45deg',
                    ease: 'expo.out',
                },
                'rotate',
            );

        this.onClick = this.handleClick.bind(this);
        this.onClickOutside = this.handleClickOutside.bind(this);
        this.onKeydown = this.handleKeydown.bind(this);
        this.onFocus = this.handleFocus.bind(this);

        this.attachEvents();

        let timeout = false;
        window.addEventListener('resize', () => {
            clearTimeout(timeout);
            timeout = setTimeout(this.handleResize.bind(this), 300);
        });
    }

    static mergeSettings(options) {
        const settings = {
            globalNavIcon: {
                selector: '.global-nav-icon',
                el: null,
            },
            globalNavIconTopLine: {
                selector: '.global-nav-icon__line--top',
                el: null,
            },
            globalNavIconMiddleLine: {
                selector: '.global-nav-icon__line--middle',
                el: null,
            },
            globalNavIconBottomLine: {
                selector: '.global-nav-icon__line--bottom',
                el: null,
            },
        };

        const userSttings = options;
        for (const attrname in userSttings) {
            settings[attrname] = userSttings[attrname];
        }

        return settings;
    }

    calcIconDims() {
        // CSSプロパティ値を取得。
        this.globalNavIconWidth = getComputedStyle(this.globalNavIconEl).getPropertyValue('--global-nav-icon-width');
        this.globalNavIconHeight = getComputedStyle(this.globalNavIconEl).getPropertyValue('--global-nav-icon-height');
        this.globalNavIconLineWeight = getComputedStyle(this.globalNavIconTopLineEl).getPropertyValue('--global-nav-icon-line-weight');

        // 値と単位に分ける。
        this.globalNavIconWidthUnitAndValue = stripUnit(this.globalNavIconWidth);
        this.globalNavIconHeightUnitAndValue = stripUnit(this.globalNavIconHeight);
        this.globalNavIconLineWeightUnitAndValue = stripUnit(this.globalNavIconLineWeight);

        // 値と単位の型変換。
        this.globalNavIconWidthValue = this.globalNavIconWidthUnitAndValue ? parseFloat(this.globalNavIconWidthUnitAndValue[0]) : 0;
        this.globalNavIconHeightValue = this.globalNavIconHeightUnitAndValue ? parseFloat(this.globalNavIconHeightUnitAndValue[0]) : 0;
        this.globalNavIconHeightUnit = this.globalNavIconHeightUnitAndValue ? this.globalNavIconHeightUnitAndValue[1] : '';
        this.globalNavIconLineWeightValue = this.globalNavIconLineWeightUnitAndValue ? parseFloat(this.globalNavIconLineWeightUnitAndValue[0]) : 0;
        this.globalNavIconLineWeightUnit = this.globalNavIconLineWeightUnitAndValue ? this.globalNavIconLineWeightUnitAndValue[1] : '';

        if (this.globalNavIconHeightUnit !== this.globalNavIconLineWeightUnit) {
            throw new Error('this.globalNavIconHeightValue and this.globalNavIconLineWeightUnit are not same.');
        }
    }

    attachEvents() {
        this.globalNav.triggerEl.addEventListener('click', this.onClick);
        this.globalNav.containerEl.addEventListener('click', this.onClickOutside);
        window.addEventListener('keydown', this.onKeydown);
        window.addEventListener('focus', this.onFocus, true);
    }

    handleClick() {
        if ('false' === this.globalNav.triggerEl.getAttribute('aria-expanded')) {
            this.open();
        } else if ('true' === this.globalNav.triggerEl.getAttribute('aria-expanded')) {
            this.close();
        }
    }

    handleClickOutside(e) {
        if (!e.target.closest(this.globalNav.config.globalNav.selector)) {
            this.close();
        }
    }

    handleKeydown(e) {
        switch (e.key) {
            case 'Esc': // IE/Edge固有の値
            case 'Escape':
                this.close();
                break;
            default:
                return;
        }
    }

    handleResize() {
        this.calcIconDims();
    }

    handleFocus() {
        if ('true' === this.globalNav.triggerEl.getAttribute('aria-expanded')) {
            if (!document.activeElement.closest(this.globalNav.config.globalNav.selector) && !document.activeElement.closest(this.globalNav.config.trigger.selector)) {
                this.close();
            }
        }
    }

    onAnimateStart() {
        this.globalNavIconTopLineEl.style.willChange = 'transform';

        if (this.globalNavIconMiddleLineEl) {
            this.globalNavIconMiddleLineEl.style.willChange = 'transform';
        }

        this.globalNavIconBottomLineEl.style.willChange = 'transform';
    }

    onAnimateEnd() {
        this.globalNavIconTopLineEl.style.willChange = 'auto';

        if (this.globalNavIconMiddleLineEl) {
            this.globalNavIconMiddleLineEl.style.willChange = 'auto';
        }

        this.globalNavIconBottomLineEl.style.willChange = 'auto';
    }

    open() {
        this.timeline.restart();
    }

    close() {
        this.onAnimateStart();

        this.timeline.reverse();
    }
}

export class GlobalNav {
    constructor(options) {
        this.config = GlobalNav.mergeSettings(options);

        this.containerEl = !this.config.container.el ? document.querySelector(this.config.container.selector) : this.config.container.el;
        this.globalNavEl = !this.config.globalNav.el ? document.querySelector(this.config.globalNav.selector) : this.config.globalNav.el;
        this.triggerEl = !this.config.trigger.el ? document.querySelector(this.config.trigger.selector) : this.config.trigger.el;

        if (!this.containerEl || !this.globalNavEl || !this.triggerEl) {
            return false;
        }

        this.triggerScreenReaderTextEl = !this.config.screenReaderText.el ? this.triggerEl.querySelector(this.config.screenReaderText.selector) : this.config.screenReaderText.el;

        if (!this.triggerScreenReaderTextEl) {
            throw new Error('triggerScreenReaderTextEl not found.');
        }

        this.triggerScreenReaderOpenText = this.triggerScreenReaderTextEl.getAttribute('data-open-text') ? this.triggerScreenReaderTextEl.getAttribute('data-open-text') : 'Open';
        this.triggerScreenReaderCloseText = this.triggerScreenReaderTextEl.getAttribute('data-close-text') ? this.triggerScreenReaderTextEl.getAttribute('data-close-text') : 'Close';

        this.onClick = this.handleClick.bind(this);
        this.onClickOutside = this.handleClickOutside.bind(this);
        this.onKeydown = this.handleKeydown.bind(this);
        this.onFocus = this.handleFocus.bind(this);

        this.globalNavIcon = new GlobalNavIcon(this);

        this.attachEvents();
    }

    static mergeSettings(options) {
        const settings = {
            container: {
                selector: '.global-nav-wrapper',
                el: null,
            },
            globalNav: {
                selector: '.global-nav',
                el: null,
            },
            trigger: {
                selector: '.global-nav-button',
                el: null,
            },
            screenReaderText: {
                selector: '.screen-reader-text',
                el: null,
            },
        };

        const userSttings = options;
        for (const attrname in userSttings) {
            settings[attrname] = userSttings[attrname];
        }

        return settings;
    }

    attachEvents() {
        this.triggerEl.addEventListener('click', this.onClick);
        this.containerEl.addEventListener('click', this.onClickOutside);
        window.addEventListener('keydown', this.onKeydown);
        window.addEventListener('focus', this.onFocus, true);
    }

    handleClick(e) {
        e.preventDefault();

        const expanded = this.triggerEl.getAttribute('aria-expanded');

        if ('false' === expanded) {
            this.open();
        } else if ('true' === expanded) {
            this.close();
        }
    }

    handleClickOutside(e) {
        if (!e.target.closest(this.config.globalNav.selector)) {
            this.close();
        }
    }

    handleKeydown(e) {
        switch (e.key) {
            case 'Esc': // IE/Edge固有の値
            case 'Escape':
                this.close();
                break;
            default:
                return;
        }
    }

    handleFocus() {
        if ('true' === this.triggerEl.getAttribute('aria-expanded')) {
            if (!document.activeElement.closest(this.config.globalNav.selector) && !document.activeElement.closest(this.config.trigger.selector)) {
                this.close();
            }
        }
    }

    open() {
        this.globalNavIcon.open();

        this.triggerEl.setAttribute('aria-expanded', true);
        this.containerEl.setAttribute('data-is-open', ''); // オーバーレイを表示するトリガー
        this.triggerScreenReaderTextEl.textContent = this.triggerScreenReaderCloseText; // スクリーンリーダーテキストを更新

        // スクロールを無効にする（グロナビの発火ボタンがスクロールされてしまうのを防ぐため）
        document.body.style.overflow = 'hidden';

        gsap.to(this.globalNavEl, {
            x: '0',
            visibility: 'visible',
            duration: 0.6,
            ease: DEFAULT_EASE,
            onStart: () => {
                this.globalNavEl.style.willChange = 'transform';
            },
            onComplete: () => {
                this.globalNavEl.style.willChange = 'auto';
            },
        });
    }

    close() {
        this.globalNavIcon.close();

        this.triggerEl.setAttribute('aria-expanded', false);
        this.containerEl.removeAttribute('data-is-open'); // オーバーレイを表示するトリガー
        this.triggerScreenReaderTextEl.textContent = this.triggerScreenReaderOpenText; // スクリーンリーダーテキストを更新

        document.body.style.overflow = 'visible';

        gsap.to(this.globalNavEl, {
            x: '100%',
            duration: 0.6,
            ease: DEFAULT_EASE,
            onStart: () => {
                this.globalNavEl.style.willChange = 'transform';
            },
            onComplete: () => {
                this.globalNavEl.scrollTo(0, 0);
                this.globalNavEl.style.visibility = 'hidden';
                this.globalNavEl.style.willChange = 'auto';
            },
        });
    }
}
