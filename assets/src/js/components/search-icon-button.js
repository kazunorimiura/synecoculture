export class SearchIconButton {
    constructor() {
        this.searchButtonContainer = document.querySelector('.search-icon-button-container');
        if (!this.searchButtonContainer) return;
        this.searchButton = document.querySelector('.search-icon-button');
        if (!this.searchButton) return;
        this.searchButtonFormContainer = document.querySelector('.search-icon-button__modal');
        if (!this.searchButtonFormContainer) return;
        this.searchButtonForm = this.searchButtonFormContainer.querySelector('.search-form__input');

        this.isOpen = false;

        this.searchButton.addEventListener('click', this.clickHandler.bind(this));
        document.addEventListener('click', this.clickOutsideHandler.bind(this));
        window.addEventListener('keydown', this.keydownHandler.bind(this));
    }

    clickHandler() {
        if (this.isOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    clickOutsideHandler(event) {
        if (!event.target.closest('.search-icon-button') && !event.target.closest('.search-icon-button__modal__content')) {
            this.close();
        }
    }

    keydownHandler(event) {
        if (event.key === 'Escape' || event.keyCode === 27) {
            this.close();
        }
    }

    open() {
        this.searchButtonFormContainer.style.display = 'block';
        this.searchButtonForm.focus();
        this.searchButtonForm.select();
        this.isOpen = true;
        this.searchButton.setAttribute('aria-expanded', true);
    }

    close() {
        this.searchButtonFormContainer.style.display = 'none';
        this.isOpen = false;
        this.searchButton.setAttribute('aria-expanded', false);
    }
}
