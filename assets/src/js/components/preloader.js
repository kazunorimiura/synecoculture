import imagesLoaded from 'imagesloaded';
import WebFont from 'webfontloader';

/**
 * プリローダークラス
 *
 * アセットの読み込みを監視して、任意のタイミングで関数を実行できるようにします。
 *
 * @class Preloader
 */
class Preloader {
    /**
     * インスタンスを作成
     *
     * @param {object} options 設定
     * @memberof Preloader
     */
    constructor(options) {
        this.config = Preloader.mergeSettings(options);

        this.imageAssets = typeof this.config.image === 'string' ? document.querySelectorAll(this.config.image) : this.config.image;
        this.videoAssets = typeof this.config.video === 'string' ? document.querySelectorAll(this.config.video) : this.config.video;
        this.fontAssets = this.config.webfonts;

        this.step = 0;
        this.totalStep = 0;
        this.totalStep += this.imageAssets.length ? this.imageAssets.length : 0;
        this.totalStep += this.videoAssets.length ? this.videoAssets.length : 0;
        this.totalStep += this.fontAssets.length ? this.fontAssets.length : 0;
        console.log('total step:', this.totalStep);

        Promise.all([this.preloadImages(), this.preloadVideo(), this.preloadFonts()])
            .then(() => {
                this.config.onComplete();

                return;
            })
            .catch((error) => {
                throw Error(error);
            });
    }

    /**
     * 画像の読み込みを監視する
     *
     * @return {object} Promiseオブジェクトを返却
     * @memberof Preloader
     */
    preloadImages() {
        return new Promise((resolve) => {
            const imgLoaded = imagesLoaded(this.imageAssets, { background: true });

            imgLoaded.on('progress', (instance, image) => {
                console.log('image loaded: ' + image.img.src);
                this.calculateProgress();
            });

            imgLoaded.on('done', () => {
                this.config.onImagesLoaded();

                resolve();
            });
        });
    }

    /**
     * 動画（videoタグ）の読み込みを監視する
     *
     * @return {object} Promiseオブジェクトを返却
     * @memberof Preloader
     */
    preloadVideo() {
        let _loadAssets = [];

        this.videoAssets.forEach((element) => {
            _loadAssets.push(
                new Promise((resolve) => {
                    element.load();

                    element.addEventListener('loadeddata', () => {
                        console.log('video is loaded: ' + element.currentSrc);
                        this.calculateProgress();

                        resolve();
                    });
                })
            );
        });

        return new Promise((resolve) => {
            Promise.all(_loadAssets)
                .then(() => {
                    this.config.onVideosLoaded();

                    resolve();

                    return;
                })
                .catch((error) => {
                    throw Error(error);
                });
        });
    }

    /**
     * ウェブフォントの読み込みを監視する
     *
     * @return {object} Promiseオブジェクトを返却
     * @memberof Preloader
     */
    preloadFonts() {
        let _loadAssets = [];

        this.fontAssets.forEach((element) => {
            _loadAssets.push(
                new Promise((resolve) => {
                    WebFont.load({
                        custom: {
                            families: [element],
                        },
                        timeout: 5000,
                        fontactive: (fontFamily) => {
                            console.log('webfont is loaded: ' + fontFamily);
                            this.calculateProgress();
                        },
                        fontinactive: (fontFamily) => {
                            console.log('timeout: ' + fontFamily);
                        },
                        inactive: () => {
                            resolve();
                        },
                        active: () => {
                            resolve();
                        },
                    });
                })
            );
        });

        return new Promise((resolve) => {
            Promise.all(_loadAssets)
                .then(() => {
                    this.config.onWebfontsLoaded();

                    resolve();

                    return;
                })
                .catch((error) => {
                    throw Error(error);
                });
        });
    }

    /**
     * 進捗を計算する
     *
     * @memberof Preloader
     */
    calculateProgress() {
        this.step++;
        this.progress = `${Math.round((this.step / this.totalStep) * 100)}%`;
        console.log(this.progress + ' loading...');
    }

    /**
     * デフォルトの設定をカスタムの設定で上書きする
     *
     * @param {Object} options - 任意の設定オブジェクト
     * @returns {Object} - カスタムプリローダーの設定
     */
    static mergeSettings(options) {
        const settings = {
            image: 'img',
            video: 'video',
            webfonts: [],
            onComplete: () => {
                console.log('all loader is complete.');
            },
            onImagesLoaded: () => {
                console.log('image loader is complete.');
            },
            onVideosLoaded: () => {
                console.log('video loader is complete.');
            },
            onWebfontsLoaded: () => {
                console.log('webfont loader is complete.');
            },
        };

        const userSettings = options;
        for (const attrname in userSettings) {
            settings[attrname] = userSettings[attrname];
        }

        return settings;
    }
}

export default Preloader;
