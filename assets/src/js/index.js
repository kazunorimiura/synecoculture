import Preloader from './components/preloader';
import IntrinsicRatioVideos from './components/intrinsic-ratio-videos';
import clickableContainer from './components/clickable-container';
import { AnchorLink } from './components/anchor-link';
import { Darkmode } from './components/darkmode';
import { GlobalNav } from './components/global-nav';
import { DropdownMenu } from './components/dropdown-menu';
import { AccordionMenu } from './components/accordion-menu';
import { Toc } from './components/toc';
import { createAccordion } from './components/accordion';
import { createSynecoSlider } from './components/syneco-slider';
import { createNewsSlider } from './components/news-slider';
import { createTrilemmaImage } from './components/trilemma-image';
import { createFeaturedProjects } from './components/featured-projects';
import { createMorePostsLoader } from './components/more-posts-loader';
import { calcVh } from './components/calc-vh';
import { FloatingSidebar } from './components/floating-sidebar';

class App {
    constructor() {
        // プリロードを開始
        this.preloader = new Preloader({
            images: document.querySelectorAll('img'),
            videos: document.querySelectorAll('video'),
            webfonts: [], // 例, 'SST W20', 'SST Japanese W55'
            onComplete: this.onLoaded.bind(this),
            onImagesLoaded: this.onImagesLoaded.bind(this),
            onVideosLoaded: this.onVideosLoaded.bind(this),
            onWebfontsLoaded: this.onWebfontsLoaded.bind(this),
        });

        this.siteHeader = document.querySelector('[data-site-header]');
        this.globalNav = new GlobalNav();

        this.anchorLinkInstances = [];

        // アンカーリンクのスムーススクロール
        const anchorLinkEls = document.querySelectorAll('a[href*="#"]');
        if (anchorLinkEls) {
            anchorLinkEls.forEach((el) => {
                // グロナビ内のアンカーリンクはコールバックでcloseイベントを発火させる必要がある
                if (el.closest('.global-nav')) {
                    this.anchorLinkInstances.push(
                        new AnchorLink({
                            a: {
                                selector: 'a',
                                el: el,
                            },
                            callbackBefore: this.globalNav.close.bind(this.globalNav),
                        }),
                    );
                }

                this.anchorLinkInstances.push(
                    new AnchorLink({
                        a: {
                            selector: 'a',
                            el: el,
                        },
                    }),
                );
            });
        }

        // 幅を記憶
        this.lastWidth = window.innerWidth;

        calcVh();

        this.attachEvents();
    }

    attachEvents() {
        // 初期化
        window.addEventListener('DOMContentLoaded', this.onInit.bind(this));

        // リサイズイベントをアタッチする。
        let timeout = false;
        window.addEventListener('resize', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                this.onResize();
            }, 300);
        });
    }

    /**
     * DOM構築後に実行する。
     */
    onInit() {
        // シネコスライダーを初期化
        createSynecoSlider();

        createNewsSlider();

        createFeaturedProjects();

        createMorePostsLoader();

        // 埋め込み動画をレスポンシブ化
        this.intrinsicRatioVideos = new IntrinsicRatioVideos();

        // ダークモードトグルを初期化する。
        new Darkmode();

        // 目次を作成する。
        const tocEls = document.querySelectorAll('.toc');
        tocEls.forEach((el) => {
            new Toc({
                container: {
                    selector: '.toc',
                    el: el,
                },
            });
        });

        // ドロップダウンメニュー
        const dropdownMenuTriggerEls = [...document.querySelectorAll('.page-menu > ul > .page_item_has_children > .menu-item-toggle'), ...document.querySelectorAll('.dropdown-menu > .navigation__list > .menu-item-has-children > .menu-item-toggle')];
        dropdownMenuTriggerEls.forEach((el) => {
            new DropdownMenu({
                container: {
                    selector: '.menu-item-has-children, .page_item_has_children',
                    el: null,
                },
                trigger: {
                    selector: '.menu-item-toggle',
                    el: el,
                },
            });
        });

        // アコーディオンメニュー
        const AccordionMenuTriggerEls = [...document.querySelectorAll('.accordion-menu .menu-item-toggle')];
        AccordionMenuTriggerEls.forEach((el) => {
            new AccordionMenu({
                container: {
                    selector: '.menu-item-has-children',
                    el: null,
                },
                trigger: {
                    selector: '.menu-item-toggle',
                    el: el,
                },
            });
        });

        // アコーディオン
        createAccordion();

        // フローティングサイドバー
        new FloatingSidebar();

        // MW WP Form: エラー発生時のa11y対応
        // エラー要素をすべて検索
        const errorElements = document.querySelectorAll('.error');
        errorElements.forEach(function (errorElement) {
            // 関連する入力フィールドを特定する必要があります
            // これはフォームの構造によって異なります
            const relatedField = errorElement.previousElementSibling;

            if (relatedField && relatedField.tagName === 'INPUT') {
                relatedField.setAttribute('aria-invalid', 'true');

                // すでに追加しているエラーIDを取得
                const errorId = errorElement.getAttribute('id');
                if (errorId) {
                    relatedField.setAttribute('aria-describedby', errorId);
                }
            }
        });

        clickableContainer();

        // トリレンマ図を作成
        createTrilemmaImage();
    }

    /**
     * ページ読み込み完了後に実行する。
     */
    onLoaded() {
        console.log('all assets are loaded.');
        document.body.classList.add('is-all-loaded');
    }

    /**
     * 画像の読み込み完了後に実行する。
     */
    onImagesLoaded() {
        console.log('image loader is complete.');
    }

    /**
     * ビデオの読み込み完了後に実行する。
     */
    onVideosLoaded() {
        console.log('video loader is complete.');
    }

    /**
     * ウェブフォントのレンダリング完了後に実行する。
     */
    onWebfontsLoaded() {
        console.log('webfont loader is complete.');
    }

    onResize() {
        const currentWidth = window.innerWidth;

        // 幅が変わった場合のみ処理（画面回転など）
        if (currentWidth !== this.lastWidth) {
            // アンカーリンクのオフセット値を更新
            const siteHeaderHeight = this.siteHeader ? this.siteHeader.clientHeight : 0;
            this.anchorLinkInstances.forEach(function (anchorLink) {
                anchorLink.updateOffset(siteHeaderHeight);
            });

            calcVh();
        }
        // 高さだけ変わった場合は無視（アドレスバー）
    }
}

new App();
