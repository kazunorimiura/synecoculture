import gsap, { Power1 } from 'gsap';
import { DEFAULT_EASE } from '../utilities';

export class AccordionMenu {
    constructor(options) {
        this.config = AccordionMenu.mergeSettings(options);

        this.triggerEl = !this.config.trigger.el ? document.querySelector(this.config.trigger.selector) : this.config.trigger.el;

        if (!this.triggerEl) {
            throw new Error('triggerEl not found.');
        }

        this.containerEl = this.triggerEl.closest(this.config.container.selector);

        if (!this.containerEl) {
            throw new Error('containerEl not found.');
        }

        this.menuEl = this.containerEl.querySelector(this.config.menu.selector);

        if (!this.menuEl) {
            throw new Error('menuEl not found.');
        }

        this.triggerScreenReaderTextEl = this.triggerEl.querySelector('.screen-reader-text');

        if (!this.triggerScreenReaderTextEl) {
            throw new Error('triggerScreenReaderTextEl not found.');
        }

        this.triggerScreenReaderOpenText = this.triggerScreenReaderTextEl.getAttribute('data-open-text') ? this.triggerScreenReaderTextEl.getAttribute('data-open-text') : 'Open';
        this.triggerScreenReaderCloseText = this.triggerScreenReaderTextEl.getAttribute('data-close-text') ? this.triggerScreenReaderTextEl.getAttribute('data-close-text') : 'Close';

        this.onClick = this.handleClick.bind(this);
        this.onFocus = this.handleFocus.bind(this);

        this.attachEvents();
    }

    static mergeSettings(options) {
        const settings = {
            container: {
                selector: '.menu-item-has-children',
                el: null,
            },
            trigger: {
                selector: '.menu-item-toggle',
                el: null,
            },
            menu: {
                selector: '.sub-menu-wrapper',
                el: null,
            },
            screenReaderText: {
                selector: '.screen-reader-text',
                el: null,
            },
            pseudoTriggerEl: {
                selector: '[href="#menu"], [href="#pll_switcher"]',
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
        window.addEventListener('focus', this.onFocus, true);

        // 特定のa要素もトリガー化する（項目をクリックしやすくするため）。
        const pseudoTriggerEl = this.containerEl.querySelector(this.config.pseudoTriggerEl.selector);

        if (pseudoTriggerEl) {
            pseudoTriggerEl.addEventListener('click', this.onClick);
        }
    }

    handleClick(e) {
        e.preventDefault();

        const triggerEl = e.currentTarget.closest(this.config.container.selector).querySelector(this.config.trigger.selector);

        console.log('triggerEl', triggerEl);

        if ('false' === triggerEl.getAttribute('aria-expanded')) {
            this.open();
        } else if ('true' === triggerEl.getAttribute('aria-expanded')) {
            this.close();
        }
    }

    handleFocus() {
        if ('true' === this.triggerEl.getAttribute('aria-expanded')) {
            if (!document.activeElement.closest(this.config.container.selector)) {
                this.close();
            }
        }
    }

    open() {
        this.triggerEl.setAttribute('aria-expanded', true);
        this.triggerScreenReaderTextEl.textContent = this.triggerScreenReaderCloseText;

        gsap.to(this.triggerEl, {
            rotate: '180deg',
            duration: 0.3,
            ease: DEFAULT_EASE,
        });

        gsap.to(this.menuEl, {
            display: 'block',
        });
        gsap.to(this.menuEl, {
            height: 'auto',
            duration: 0.3,
            ease: Power1.easeOut,
        });
    }

    close() {
        this.triggerEl.setAttribute('aria-expanded', false);
        this.triggerScreenReaderTextEl.textContent = this.triggerScreenReaderOpenText;

        gsap.to(this.triggerEl, {
            rotate: '0deg',
            duration: 0.3,
            ease: DEFAULT_EASE,
        });

        gsap.to(this.menuEl, {
            height: 0,
            duration: 0.3,
            ease: Power1.easeOut,
            onComplete: () => {
                gsap.set(this.menuEl, { display: 'none' });
            },
        });
    }
}
