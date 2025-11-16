const hasProperty = (obj, path) => path.split('.').reduce((o, key) => (o && o[key] !== undefined ? o[key] : undefined), obj) !== undefined;

class MorePostsLoader {
    constructor(triggerEl) {
        this.triggerEl = typeof triggerEl === 'string' ? document.querySelector(triggerEl) : triggerEl;
        this.containerEl = document.getElementById(triggerEl.dataset.containerId);
        this.triggerText = triggerEl.textContent;
        this.triggerLoadingText = 'Loading…';
        this.triggerUrl = triggerEl.dataset.moreLink;
        this.nextPageNum = parseInt(this.getPageParameterValue(this.triggerUrl));
        this.maxPageNum = parseInt(triggerEl.dataset.maxPageNum);
        this.template = triggerEl.dataset.template;

        this.triggerEl.addEventListener('click', this.loadMore.bind(this));
    }

    async loadMore() {
        try {
            this.triggerEl.textContent = this.triggerLoadingText;
            const endpoint = `${this.triggerUrl}`;
            console.log('endpoint', endpoint);
            const response = await fetch(endpoint);
            this.triggerEl.textContent = this.triggerText;
            if (!response.ok) {
                throw new Error('Network response was not ok');
            }
            const data = await response.json();
            this.appendContent(data);
            this.nextPageNum++;
            this.triggerUrl = this.updatePageParameter(this.triggerUrl, this.nextPageNum); // URLのページ番号を更新
            if (this.maxPageNum < this.nextPageNum) {
                this.triggerEl.closest('.paginate-posts-more-buttom').style.display = 'none';
            }
        } catch (error) {
            console.error('There was a problem with the fetch operation:', error);
        }
    }

    appendContent(data) {
        let firstFocusableElement = null;

        data.forEach((item) => {
            console.log('item', item);
            const div = this.createTemplate(item);
            if (div) {
                this.containerEl.appendChild(div);

                // 追加した要素内の最初のフォーカス可能な要素を探す
                if (!firstFocusableElement) {
                    const focusableElement = div.querySelector('a:not([aria-hidden="true"])');
                    if (focusableElement) {
                        firstFocusableElement = focusableElement;
                    }
                }
            }
        });

        // 現在のページ番号を更新
        if (this.maxPageNum >= this.nextPageNum + 1) {
            this.triggerEl.setAttribute('data-next-page-num', this.nextPageNum + 1);
        }

        // 最初のフォーカス可能な要素にフォーカスを当てる
        if (firstFocusableElement) {
            firstFocusableElement.focus();
        }
    }

    getPageParameterValue(url) {
        // 正規表現で 'page' パラメータの値を抽出
        let match = url.match(/[?&]page=([^&]*)/);

        // マッチした場合は値を返す。マッチしない場合は null を返す
        return match ? match[1] : null;
    }

    updatePageParameter(url, newPageValue) {
        // 'page'パラメータを新しい値に変更
        let newUrl;

        // 'page'パラメータが存在する場合
        if (url.match(/([?&])page=([^&]*)/)) {
            newUrl = url.replace(/([?&])page=([^&]*)/, `$1page=${newPageValue}`);
        } else {
            // 'page'パラメータが存在しない場合、追加する
            newUrl = url.includes('?') ? `${url}&page=${newPageValue}` : `${url}?page=${newPageValue}`;
        }

        return newUrl;
    }

    /**
     * HTMLエンティティを含む文字列をデコードしてプレーンテキストに変換する。
     *
     * @param {string} text デコードするHTMLエンティティを含む文字列。
     * @returns {string} デコードされたプレーンテキストの文字列。
     */
    decode(text) {
        const parser = new DOMParser();
        return parser.parseFromString(text, 'text/html').body.textContent;
    }

    /**
     * テンプレートスラッグに基づき、投稿アイテムのHTMLテンプレートを生成する。
     *
     * @param {{[key: string]: any}} item HTTPレスポンスに含まれる投稿アイテムオブジェクト。
     * @return {HTMLElement} HTMLテンプレート。
     * @memberof MorePostsLoader
     */
    createTemplate(item) {
        const args = {
            postId: item.id,
            title: this.decode(item.title),
            link: this.decode(item.link),
            excerpt: this.decode(item.excerpt),
            date: this.decode(item.date),
            date_w3c: this.decode(item.date_w3c),
            thumbnail: item.featured_image_src,
            meta: item.meta,
        };

        ['category', 'post_tag', 'blog_cat', 'blog_tag', 'project_cat', 'project_domain', 'project_tag', 'area', 'case_study_tag', 'member_cat', 'member_tag', 'glossary_tag', 'career_cat', 'career_tag'].forEach((key) => {
            if (item[key]) {
                args[key] = item[key];
            }
        });

        return this[`template__${this.template}`](args);
    }

    /**
     * 画像をプリロードし、HTML要素を返す。
     *
     * @param {{[key: any]: any}} imageSrc 画像オブジェクト。
     * @param {string} containerId 画像のコンテナ要素のID。
     * @return {string} HTML要素の文字列。
     */
    preloadImage(imageSrc, containerId) {
        let element = '';

        if (imageSrc.constructor === Object && Object.keys(imageSrc).length !== 0) {
            const imageObj = {
                width: imageSrc.width,
                height: imageSrc.height,
                src: imageSrc.src,
                srcset: imageSrc.srcset,
                sizes: imageSrc.sizes,
                alt: imageSrc.alt,
            };

            // 画像をプリロードする
            const img = new Image();
            img.src = imageObj.src;
            if (imageObj.width) {
                img.width = imageObj.width;
            }
            if (imageObj.height) {
                img.height = imageObj.height;
            }
            if (imageObj.srcset) {
                img.srcset = imageObj.srcset;

                if (imageObj.sizes) {
                    img.sizes = imageObj.sizes;
                }
            }
            if (imageObj.alt) {
                img.alt = imageObj.alt;
            } else {
                img.alt = '';
            }

            // プリロードが完了したらDOMに追加する
            img.onload = () => {
                const containerElement = this.containerEl.querySelector(`[data-post-element-id="${containerId}"]`);
                if (containerElement) {
                    containerElement.innerHTML = '';
                    containerElement.appendChild(img);
                }
            };

            // プリロードが失敗した場合のエラーハンドリング
            img.onerror = () => {
                console.error('画像の読み込みに失敗しました:', imageObj.src);
            };
        }

        return element;
    }

    /**
     * `news_blog` テンプレートのHTMLを生成する。
     * このメソッドで構築されるHTMLは `includes/class-wpf-posts.php` で定義された各テンプレートの構造に倣っている。
     *
     * @param {{[key: string]: any}} args テンプレートに使用する固有データのオブジェクト。HTML属性値、テキストコンテンツなどが含まれる。
     * @return {HTMLElement} `news_blog` テンプレートスラッグのHTMLテンプレート。
     * @memberof MorePostsLoader
     */
    template__news_blog(args) {
        // container
        const containerEl = document.createElement('article');
        containerEl.setAttribute('class', 'news-posts__item');

        // inner
        const innerEl = document.createElement('div');
        innerEl.setAttribute('class', 'news-posts__item__inner');

        // main
        const mainEl = document.createElement('div');
        mainEl.setAttribute('class', 'news-posts__item__main');

        // category
        const optionalElsKeys = ['category', 'blog_cat'];
        console.log('args', args);
        if (optionalElsKeys.some((key) => hasProperty(args, key))) {
            // categories
            const catgoriesEl = document.createElement('div');
            catgoriesEl.setAttribute('class', 'news-posts__item__main-categories');

            // category link
            const catEl = document.createElement('a');
            catEl.setAttribute('class', 'news-posts__item__main-category pill');

            console.log('args.category', args.category);
            console.log('args.blog_cat', args.blog_cat);

            if (args.category) {
                args.category.forEach((term) => {
                    catEl.setAttribute('href', term.link);
                    catEl.textContent = term.name;

                    // append
                    catgoriesEl.appendChild(catEl);
                });
            }
            if (args.blog_cat) {
                args.blog_cat.forEach((term) => {
                    catEl.setAttribute('href', term.link);
                    catEl.textContent = term.name;

                    // append
                    catgoriesEl.appendChild(catEl);
                });
            }

            // append
            catgoriesEl.appendChild(catEl);

            // append
            mainEl.appendChild(catgoriesEl);
        }

        // title
        const titleEl = document.createElement('a');
        titleEl.setAttribute('href', args.link);
        titleEl.setAttribute('class', 'news-posts__item__title');
        titleEl.textContent = args.title;
        mainEl.appendChild(titleEl);

        // date
        const dateContainerEl = document.createElement('div');
        dateContainerEl.setAttribute('class', 'news-posts__item__date');
        dateContainerEl.setAttribute('style', '--flow-space: var(--space-s-space)');
        const dateEl = document.createElement('time');
        dateEl.setAttribute('datetime', args.date_w3c);
        dateEl.textContent = args.date;
        dateContainerEl.appendChild(dateEl);
        mainEl.appendChild(dateContainerEl);

        // append
        innerEl.appendChild(mainEl);

        // thumbnail
        const thumbnailEl = document.createElement('a');
        thumbnailEl.setAttribute('data-post-element-id', `thumbnail-${args.postId}`);
        thumbnailEl.setAttribute('href', args.link);
        thumbnailEl.setAttribute('title', args.title);
        thumbnailEl.setAttribute('class', 'news-posts__item__thubmnail frame');
        thumbnailEl.setAttribute('aria-hidden', 'true');
        thumbnailEl.setAttribute('tabindex', '-1');
        thumbnailEl.innerHTML = this.preloadImage(args.thumbnail, `thumbnail-${args.postId}`);

        // append
        innerEl.appendChild(thumbnailEl);

        // append
        containerEl.appendChild(innerEl);

        return containerEl;
    }

    /**
     * `projects` テンプレートのHTMLを生成する。
     * このメソッドで構築されるHTMLは `includes/class-wpf-posts.php` で定義された各テンプレートの構造に倣っている。
     *
     * @param {{[key: string]: any}} args テンプレートに使用する固有データのオブジェクト。HTML属性値、テキストコンテンツなどが含まれる。
     * @return {HTMLElement} `projects` テンプレートスラッグのHTMLテンプレート。
     * @memberof MorePostsLoader
     */
    template__projects(args) {
        // container
        const containerEl = document.createElement('article');
        containerEl.setAttribute('class', 'project-posts__item');

        // inner
        const innerEl = document.createElement('div');
        innerEl.setAttribute('class', 'project-posts__item__inner');

        // main
        const mainEl = document.createElement('div');
        mainEl.setAttribute('class', 'project-posts__item__main');

        // category
        let optionalElsKeys = ['project_cat'];
        if (optionalElsKeys.some((key) => hasProperty(args, key))) {
            optionalElsKeys = ['main'];
            if (optionalElsKeys.some((key) => hasProperty(args.project_cat, key))) {
                // categories
                const catgoriesEl = document.createElement('div');
                catgoriesEl.setAttribute('class', 'project-posts__item__main-categories');

                // category link
                const catEl = document.createElement('a');
                catEl.setAttribute('class', 'project-posts__item__main-category pill');
                if (args.project_cat.main) {
                    args.project_cat.main.forEach((term) => {
                        catEl.setAttribute('href', term.link);
                        catEl.textContent = term.name;

                        // append
                        catgoriesEl.appendChild(catEl);
                    });
                }

                // append
                catgoriesEl.appendChild(catEl);

                // append
                mainEl.appendChild(catgoriesEl);
            }
        }

        // title
        const titleEl = document.createElement('a');
        titleEl.setAttribute('href', args.link);
        titleEl.setAttribute('class', 'project-posts__item__title');
        titleEl.textContent = args.title;
        mainEl.appendChild(titleEl);

        // sub category
        optionalElsKeys = ['project_cat'];
        if (optionalElsKeys.some((key) => hasProperty(args, key))) {
            optionalElsKeys = ['sub'];
            if (optionalElsKeys.some((key) => hasProperty(args.project_cat, key))) {
                // categories
                const catgoriesEl = document.createElement('div');
                catgoriesEl.setAttribute('class', 'project-posts__item__sub-categories');

                // category links
                if (args.project_cat.sub) {
                    args.project_cat.sub.forEach((term) => {
                        const catEl = document.createElement('a');
                        catEl.setAttribute('class', 'project-posts__item__sub-category pill-secondary');
                        catEl.setAttribute('href', term.link);
                        catEl.textContent = term.name;

                        // append
                        catgoriesEl.appendChild(catEl);
                    });
                }

                // append
                mainEl.appendChild(catgoriesEl);
            }
        }

        // append
        innerEl.appendChild(mainEl);

        // thumbnail
        const thumbnailEl = document.createElement('a');
        thumbnailEl.setAttribute('data-post-element-id', `thumbnail-${args.postId}`);
        thumbnailEl.setAttribute('href', args.link);
        thumbnailEl.setAttribute('title', args.title);
        thumbnailEl.setAttribute('class', 'project-posts__item__thubmnail frame');
        thumbnailEl.setAttribute('aria-hidden', 'true');
        thumbnailEl.setAttribute('tabindex', '-1');
        thumbnailEl.innerHTML = this.preloadImage(args.thumbnail, `thumbnail-${args.postId}`);

        // append
        innerEl.appendChild(thumbnailEl);

        // append
        containerEl.appendChild(innerEl);

        return containerEl;
    }
}

export function createMorePostsLoader() {
    const triggerEls = document.querySelectorAll('[data-more-link]');
    triggerEls.forEach(function (el) {
        new MorePostsLoader(el);
    });
}
