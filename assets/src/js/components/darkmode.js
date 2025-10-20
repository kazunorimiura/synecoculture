import { getCookie } from '../utilities';

export class Darkmode {
    constructor(el = '[data-darkmode-toggle]') {
        this.button = typeof el === 'string' ? document.querySelector(el) : el;

        if (!this.button) {
            return;
        }

        const cookieSiteTheme = getCookie('wpf_site_theme');

        if (cookieSiteTheme) {
            if ('dark' === cookieSiteTheme) {
                this.button.querySelector('input[type="checkbox"]').checked = true;
                document.documentElement.setAttribute('data-site-theme', 'dark');
            } else if ('light' === cookieSiteTheme) {
                this.button.querySelector('input[type="checkbox"]').checked = false;
                document.documentElement.setAttribute('data-site-theme', 'light');
            }
        } else {
            // Cookieがない場合はデフォルトをライトモードに設定
            this.button.querySelector('input[type="checkbox"]').checked = false;
            document.documentElement.setAttribute('data-site-theme', 'light');
        }

        this.onChange = this.handleChange.bind(this);

        this.attachEvents();
    }

    attachEvents() {
        this.button.addEventListener('change', this.onChange);
    }

    handleChange() {
        if ('light' === document.documentElement.getAttribute('data-site-theme')) {
            document.documentElement.setAttribute('data-site-theme', 'dark');
        } else if ('dark' === document.documentElement.getAttribute('data-site-theme')) {
            document.documentElement.setAttribute('data-site-theme', 'light');
        }

        const theme = document.documentElement.getAttribute('data-site-theme');

        document.cookie = `wpf_site_theme=${theme};max-age=${60 * 60 * 24 * 7};path=/`;
    }
}
