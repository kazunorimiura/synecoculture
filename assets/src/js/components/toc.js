export class Toc {
    constructor(options) {
        this.config = Toc.mergeSettings(options);

        this.containerEl = !this.config.container.el ? document.querySelector(this.config.container.selector) : this.config.container.el;

        if (!this.containerEl) {
            throw new Error('containerEl not found.');
        }

        this.anchorEls = this.containerEl.querySelectorAll(this.config.anchor.selector);

        if (!this.anchorEls) {
            throw new Error('anchorEls not found.');
        }

        this.headingEls = [];
        this.anchorEls.forEach((el) => {
            const targetId = el.getAttribute('href');
            const headingEl = document.querySelector(targetId);
            this.headingEls.push(headingEl);
        });

        if (!this.headingEls) {
            throw new Error('headingEls not found.');
        }

        // 見出し要素を監視対象にする。
        if (this.headingEls) {
            const self = this;
            const options = {
                root: null,
                rootMargin: '0px',
                threshold: 0,
            };
            const callback = (entries) => {
                entries.forEach((entry) => {
                    self.detectVisibleElements(entry);
                    self.highlightFirstActive();
                });
            };
            const observer = new IntersectionObserver(callback, options);
            this.headingEls.forEach((headingEl) => {
                observer.observe(headingEl);
            });
        }
    }

    static mergeSettings(options) {
        const settings = {
            container: {
                selector: '.toc',
                el: null,
            },
            anchor: {
                selector: '.toc a',
                el: null,
            },
            heading: {
                selector: 'article h1[id], article h2[id], article h3[id], article h4[id], article h5[id], article h6[id]',
                el: null,
            },
            screenReaderText: {
                selector: '.screen-reader-text',
                el: null,
            },
            active: {
                selector: 'is-active',
            },
            visible: {
                selector: 'is-visible',
            },
        };

        const userSttings = options;
        for (const attrname in userSttings) {
            settings[attrname] = userSttings[attrname];
        }

        return settings;
    }

    /**
     * ビューポート領域に表示されているセクションを検出する。
     * @param {HTMLElement} entry tocリンク要素。
     */
    detectVisibleElements(entry) {
        const id = entry.target.getAttribute('id');

        if (entry.isIntersecting) {
            this.containerEl.querySelector(`a[href="#${id}"]`).classList.add(this.config.visible.selector);
            this.previousSection = entry.target.getAttribute('id');
        } else {
            this.containerEl.querySelector(`a[href="#${id}"]`).classList.remove(this.config.visible.selector);
        }
    }

    /**
     * ビューポート領域に表示されているセクションのうち、最初の要素をハイライト対象とする。
     */
    highlightFirstActive() {
        // ビューポート領域に表示されているセクションのうち、最初の要素をハイライト対象とする。
        let firstVisibleLink = this.containerEl.querySelector(this.config.visible.selector);

        this.anchorEls.forEach((el) => {
            el.classList.remove(this.config.active.selector);
        });

        if (firstVisibleLink) {
            firstVisibleLink.classList.add(this.config.active.selector);
        }
        if (!firstVisibleLink && this.previousSection) {
            this.containerEl.querySelector(`a[href="#${this.previousSection}"]`).classList.add(this.config.active.selector);
        }
    }
}
