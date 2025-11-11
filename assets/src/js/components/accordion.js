class Accordion {
    constructor(domNode) {
        this.rootEl = domNode;
        this.buttonEl = this.rootEl.querySelector('button[aria-expanded], [role="button"][aria-expanded]');
        this.srTextElement = this.buttonEl.querySelector('.screen-reader-text');

        const controlsId = this.buttonEl.getAttribute('data-acc-target');

        this.contentEl = document.getElementById(controlsId);

        this.open = this.buttonEl.getAttribute('aria-expanded') === 'true';

        // add event listeners
        this.buttonEl.addEventListener('click', this.onButtonClick.bind(this));
    }

    onButtonClick() {
        this.toggle(!this.open);
    }

    toggle(open) {
        // don't do anything if the open state doesn't change
        if (open === this.open) {
            return;
        }

        // update the internal state
        this.open = open;

        const openText = this.buttonEl.dataset.openText || '開く';
        const closeText = this.buttonEl.dataset.closeText || '閉じる';

        // handle DOM updates
        this.buttonEl.setAttribute('aria-expanded', `${open}`);
        if (open) {
            this.contentEl.removeAttribute('hidden');
            this.contentEl.style.display = 'block';

            if (this.srTextElement) {
                this.srTextElement.textContent = closeText;
            }
        } else {
            this.contentEl.style.removeProperty('display');

            if (this.srTextElement) {
                this.srTextElement.textContent = openText;
            }
        }
    }

    // Add public open and close methods for convenience
    open() {
        this.toggle(true);
    }

    close() {
        this.toggle(false);
    }
}

/**
 * `Accordion` クラスのファクトリ関数
 *
 * @export
 * @return {void}
 */
export function createAccordion() {
    const accordions = document.querySelectorAll('.accordion, .manual-main__content__sidebar, .manual-nav');

    accordions.forEach((accordionEl) => {
        new Accordion(accordionEl);
    });
}
