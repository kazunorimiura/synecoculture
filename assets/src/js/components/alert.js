export class Alert {
    constructor(options) {
        this.config = Alert.mergeSettings(options);
        this.onClick = this.handleClick;
    }

    static mergeSettings(options) {
        const settings = {
            message: 'Success!',
            classes: 'notice',
            styles: null,
            hideDelay: 0,
        };

        const userSttings = options;
        for (const attrname in userSttings) {
            settings[attrname] = userSttings[attrname];
        }

        return settings;
    }

    create() {
        const alertEl = document.createElement('div');
        if (this.config.classes) {
            alertEl.setAttribute('class', this.config.classes);
        }
        if (this.config.styles) {
            alertEl.setAttribute('style', 'cursor: pointer; ' + this.config.styles);
        }

        const messageEl = document.createElement('p');
        messageEl.textContent = this.config.message;

        alertEl.append(messageEl);
        alertEl.addEventListener('click', this.onClick);

        if (this.config.hideDelay > 0) {
            setTimeout(() => {
                alertEl.remove();
            }, this.config.hideDelay);
        }

        return alertEl;
    }

    handleClick() {
        this.remove();
    }
}
