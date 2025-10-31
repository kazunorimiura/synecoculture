export class FloatingSidebar {
    /**
     * コンストラクタ
     */
    constructor() {
        // プロパティの初期化
        this.sidebarElements = null;
        this.mainContent = null;
        this.footer = null;
        this.scrollHandlers = [];

        // ページ読み込み時に初期化
        window.addEventListener('load', () => this.initialize());
    }

    /**
     * ページ読み込み時の初期化
     */
    initialize() {
        // DOM要素を取得
        this.mainContent = document.querySelector('.manual-main__content__body');
        this.sidebarElements = document.querySelectorAll('.manual-main__content__sidebar');
        this.footer = document.querySelector('.manual-footer');

        // 必要な要素が存在しない場合は終了
        if (!this.mainContent || !this.sidebarElements.length || !this.footer) {
            return;
        }

        // 各サイドバーの処理を設定
        this.sidebarElements.forEach((sidebar) => {
            // スクロールハンドラーを作成して登録
            const scrollHandler = this.createScrollHandler(sidebar);
            this.scrollHandlers.push(scrollHandler);
            window.addEventListener('scroll', scrollHandler);

            // サイドバーがメインコンテンツの隣接要素か判定
            const isAdjacentToMain = sidebar.previousElementSibling === this.mainContent || sidebar.nextElementSibling === this.mainContent;

            // ResizeObserverでサイドバーのサイズ変更を監視
            if ('ResizeObserver' in window && isAdjacentToMain) {
                new ResizeObserver((entries) => {
                    for (const entry of entries) {
                        // フローティング状態で固定されていない場合
                        if (sidebar.classList.contains('is-floating-sidebar') && !sidebar.classList.contains('is-fixed-sidebar')) {
                            const sidebarHeight = entry.contentRect.height;

                            if (sidebarHeight) {
                                // メインコンテンツの最小高さを設定
                                this.mainContent.style.setProperty('min-height', `${sidebarHeight}px`);
                            }
                        }
                    }
                }).observe(sidebar);
            }
        });

        // 初期化とリサイズイベントの設定
        this.initializeSidebars();
        window.addEventListener('resize', () => this.initializeSidebars());
    }

    /**
     * レスポンシブ対応とサイドバーの初期化
     */
    initializeSidebars() {
        // 各サイドバーのレスポンシブ設定
        this.sidebarElements.forEach((sidebar) => {
            const breakpoint = this.getCSSPropertyValue('--breakpoint-xl');

            // ブレークポイントに基づいてフローティング状態を切り替え
            const isAboveBreakpoint = window.matchMedia(`(min-width: ${breakpoint}px)`).matches;

            sidebar.classList.toggle('is-floating-sidebar', isAboveBreakpoint);

            // display:noneの場合はrevertに設定
            if (window.getComputedStyle(sidebar).display === 'none') {
                sidebar.style.setProperty('display', 'revert');
            }
        });

        // すべてのスクロールハンドラーを実行
        this.scrollHandlers.forEach((handler) => handler());
    }

    /**
     * CSSカスタムプロパティの値を取得
     */
    getCSSPropertyValue(propertyName, element = document.body) {
        const value = window.getComputedStyle(element).getPropertyValue(propertyName);

        if (value === '0') return 0;
        if (value.slice(-2) === 'px') {
            return Number(value.replace('px', ''));
        }
        return value;
    }

    /**
     * サイドバーのスクロール処理を作成
     */
    createScrollHandler(sidebar) {
        let rafId = null;

        return () => {
            // 既にrAFが予約されていればキャンセル
            if (rafId) {
                cancelAnimationFrame(rafId);
            }

            // 次のフレームで処理を実行
            rafId = requestAnimationFrame(() => {
                rafId = null;

                // フローティング状態でない場合は何もしない
                if (!sidebar.classList.contains('is-floating-sidebar')) {
                    return false;
                }

                const { scrollY, innerHeight } = window;
                const adjustedScrollY = scrollY;
                const navOffset = this.getCSSPropertyValue('--local-nav-offset', sidebar);
                const shouldFixSidebar = this.mainContent.getBoundingClientRect().top <= navOffset;

                sidebar.classList.toggle('is-fixed-sidebar', shouldFixSidebar);

                const hasReachedFooter = adjustedScrollY + innerHeight > this.footer.offsetTop;

                if (shouldFixSidebar && hasReachedFooter) {
                    const maxHeight = this.footer.offsetTop - adjustedScrollY - sidebar.offsetTop;
                    sidebar.style.setProperty('height', `${maxHeight}px`);
                } else {
                    sidebar.style.removeProperty('height');
                }
            });
        };
    }

    /**
     * クリーンアップ（必要に応じて）
     */
    destroy() {
        this.scrollHandlers.forEach((handler) => {
            window.removeEventListener('scroll', handler, { passive: true });
        });
        window.removeEventListener('resize', () => this.initializeSidebars());
    }
}
