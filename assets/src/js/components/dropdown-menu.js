import gsap from 'gsap';
import { DEFAULT_EASE, POPUP_MENU_EASE, isTouch } from '../utilities';

export class DropdownMenu {
    constructor(options) {
        this.config = DropdownMenu.mergeSettings(options);

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

        this.triggerScreenReaderTextEl = !this.config.screenReaderText.el ? this.triggerEl.querySelector(this.config.screenReaderText.selector) : this.config.screenReaderText.el;

        if (!this.triggerScreenReaderTextEl) {
            throw new Error('triggerScreenReaderTextEl not found.');
        }

        this.childMenuContainerEls = this.menuEl.querySelectorAll(this.config.container.selector);

        this.onMouseenter = this.handleMouseenter.bind(this);
        this.onMouseleave = this.handleMouseleave.bind(this);
        this.onClick = this.handleClick.bind(this);
        this.onClickOutside = this.handleClickOutside.bind(this);

        // 幅を記憶
        this.lastWidth = window.innerWidth;

        let timeout = false;
        window.addEventListener('resize', () => {
            clearTimeout(timeout);
            timeout = setTimeout(this.handleResize.bind(this), 300);
        });

        this.init();
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

    init() {
        this.attachEvents();
        this.setMenuPosition();
    }

    attachEvents() {
        if (!isTouch()) {
            this.containerEl.addEventListener('mouseenter', this.onMouseenter);
            this.containerEl.addEventListener('mouseleave', this.onMouseleave);
        }

        this.triggerEl.addEventListener('click', this.onClick);

        // 特定のa要素もトリガー化する（項目をクリックしやすくするため）。
        const pseudoTriggerEl = this.containerEl.querySelector(this.config.pseudoTriggerEl.selector);

        if (pseudoTriggerEl) {
            pseudoTriggerEl.addEventListener('click', this.onClick);
        }

        gsap.set(this.menuEl, {
            y: 15,
            autoAlpha: 0,
            display: 'none',
        });

        // 祖先に属する全ての下層メニューにイベントをアタッチ。
        this.childMenuContainerEls.forEach((el) => {
            if (!isTouch()) {
                el.addEventListener('mouseenter', this.onMouseenter);
                el.addEventListener('mouseleave', this.onMouseleave);
            }

            el.querySelector(this.config.trigger.selector).addEventListener('click', this.onClick);

            gsap.set(el.querySelector(this.config.menu.selector), {
                y: 15,
                autoAlpha: 0,
                display: 'none',
            });
        });

        document.addEventListener('click', this.onClickOutside);
    }

    detachEvents() {
        this.containerEl.removeEventListener('mouseenter', this.onMouseenter);
        this.containerEl.removeEventListener('mouseleave', this.onMouseleave);
        this.triggerEl.removeEventListener('click', this.onClick);

        this.childMenuContainerEls.forEach((el) => {
            el.removeEventListener('mouseenter', this.onMouseenter);
            el.removeEventListener('mouseleave', this.onMouseleave);
            el.querySelector(this.config.trigger.selector).removeEventListener('click', this.onClick);
        });

        document.removeEventListener('click', this.onClickOutside);
    }

    open(containerEl) {
        const triggerEl = containerEl.querySelector(this.config.trigger.selector);
        const menuEl = containerEl.querySelector(this.config.menu.selector);

        triggerEl.setAttribute('aria-expanded', true);

        gsap.to(triggerEl, {
            rotate: '180deg',
            duration: 0.5,
            ease: DEFAULT_EASE,
        });

        gsap.to(menuEl, {
            y: 0,
            autoAlpha: 1,
            display: 'block', // DDメニューの子項目がビューポートをはみ出すとき、DDメニューが開いたときだけ横スクロールを有効にする。
            ease: POPUP_MENU_EASE,
            duration: 0.5,
        });

        containerEl.classList.add('is-open');
    }

    close(containerEl) {
        const triggerEl = containerEl.querySelector(this.config.trigger.selector);
        const menuEl = containerEl.querySelector(this.config.menu.selector);

        triggerEl.setAttribute('aria-expanded', false);

        gsap.to(triggerEl, {
            rotate: '0deg',
            duration: 0.5,
            ease: DEFAULT_EASE,
        });

        gsap.to(menuEl, {
            y: 15,
            autoAlpha: 0,
            display: 'none', // DDメニューの子項目がビューポートをはみ出すとき、DDメニューが開いたときだけ横スクロールを有効にする。
            pointerEvents: 'none', // メニューが消えてすぐにカーソルを戻したときに再度表示されないようにする。
            ease: POPUP_MENU_EASE,
            duration: 0.5,
            onComplete: () => {
                menuEl.style.pointerEvents = 'auto';

                this.handleCompleteOnMenuAnimation();
            },
        });

        containerEl.classList.remove('is-open');
    }

    handleCompleteOnMenuAnimation() {
        return;
    }

    handleMouseenter(e) {
        const containerEl = e.currentTarget;

        this.open(containerEl);
    }

    handleMouseleave(e) {
        const containerEl = e.currentTarget;

        this.close(containerEl);
    }

    handleClick(e) {
        e.stopPropagation();
        e.preventDefault();

        // button要素を取得する（href="#menu"のa要素をトリガーにするケースがあるため、自身のコンテナに遡ってから、button要素を取得している）。
        const triggerEl = e.currentTarget.closest(this.config.container.selector).querySelector(this.config.trigger.selector);
        const containerEl = triggerEl.closest(this.config.container.selector);

        if ('false' === triggerEl.getAttribute('aria-expanded')) {
            this.open(containerEl);
        } else if ('true' === triggerEl.getAttribute('aria-expanded')) {
            this.close(containerEl);
        }

        // DDメニュー項目からフォーカスが外れたら、DDメニューを閉じるようにする。
        triggerEl.parentNode.querySelectorAll('a, button').forEach((focusEl) => {
            focusEl.addEventListener('blur', (e) => {
                if (!triggerEl.parentNode.contains(e.relatedTarget)) {
                    this.close(containerEl);
                }
            });
        });
    }

    handleClickOutside(e) {
        const containerEl = e.target.closest(this.config.container.selector);

        if (!containerEl) {
            this.close(this.containerEl);

            this.childMenuContainerEls.forEach((el) => {
                this.close(el);
            });
        }
    }

    handleResize() {
        const currentWidth = window.innerWidth;

        // 幅が変わった場合のみ処理（画面回転など）
        if (currentWidth !== this.lastWidth) {
            this.detachEvents();

            this.init();
        }
        // 高さだけ変わった場合は無視（アドレスバー）
    }

    setMenuPosition() {
        if (!this.containerEl) {
            return;
        }

        // サイトコンテナの高さが実際のビューポートよりも小さくなる可能性を考慮し、
        // サイトコンテナと実際のビューポートを比較して小さいほうを格納している。
        const viewportHeight = Math.min(document.body.clientHeight, document.documentElement.clientHeight);

        // サブメニューを取得
        const subMenus = this.containerEl.querySelectorAll(this.config.menu.selector);

        // 祖先サブメニューの幅。デフォルトでdispaly: noneを指定しているため、getComputedStyleで取得している。
        const subMenuWidth = parseInt(window.getComputedStyle(subMenus[0]).getPropertyValue('width'));

        // サブメニューのトータル幅
        const totalSubMenuWidth = subMenuWidth * subMenus.length;

        // サブメニューのY軸を取得
        const menuItemPosY = this.containerEl.getBoundingClientRect().top;

        // サブメニューの上辺より下側のスペースを計算
        const bottomSpace = viewportHeight - menuItemPosY;

        // サブメニューの上辺より左上側のスペースを計算
        const topSpace = viewportHeight - bottomSpace;

        // サブメニューのX軸を取得
        const menuItemPosX = this.containerEl.getBoundingClientRect().left;

        // サブメニューの左辺より右側のスペースを計算
        const rightSpace = document.documentElement.clientWidth - menuItemPosX;

        // サブメニューの左辺より左側のスペースを計算
        const leftSpace = document.documentElement.clientWidth - rightSpace;

        // 祖先サブメニューの上下のポジションを設定する。
        if (topSpace > bottomSpace) {
            this.containerEl.classList.remove('submenu-pos-top');
            this.containerEl.classList.add('submenu-pos-top');
        } else {
            this.containerEl.classList.remove('submenu-pos-bottom');
            this.containerEl.classList.add('submenu-pos-bottom');
        }

        // 祖先サブメニューの左右のポジションを設定する。
        if (totalSubMenuWidth < rightSpace) {
            this.containerEl.classList.remove('submenu-pos-right');
            this.containerEl.classList.add('submenu-pos-right');
        } else if (leftSpace > rightSpace) {
            this.containerEl.classList.remove('submenu-pos-left');
            this.containerEl.classList.add('submenu-pos-left');
        } else {
            this.containerEl.classList.remove('submenu-pos-right');
            this.containerEl.classList.add('submenu-pos-right');
        }

        // 下層サブメニューのポジションを設定する。
        this.childMenuContainerEls.forEach((containerEl) => {
            // 下層サブメニューの上下のポジションを設定する。
            if (topSpace > bottomSpace) {
                containerEl.classList.remove('submenu-pos-top');
                containerEl.classList.add('submenu-pos-top');
            } else {
                containerEl.classList.remove('submenu-pos-bottom');
                containerEl.classList.add('submenu-pos-bottom');
            }

            // 下層サブメニューの左右のポジションを設定する。
            if (totalSubMenuWidth < rightSpace) {
                containerEl.classList.remove('submenu-pos-right');
                containerEl.classList.add('submenu-pos-right');
            } else if (leftSpace > rightSpace) {
                containerEl.classList.remove('submenu-pos-left');
                containerEl.classList.add('submenu-pos-left');
            } else {
                containerEl.classList.remove('submenu-pos-right');
                containerEl.classList.add('submenu-pos-right');
            }
        });
    }
}
