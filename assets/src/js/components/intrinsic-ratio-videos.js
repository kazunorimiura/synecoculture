/**
 * レスポンシブメディア
 *
 * 埋め込み動画などのメディアをレスポンシブにします。
 *
 * @export
 * @class IntrinsicRatioVideos
 */
export default class IntrinsicRatioVideos {
    /**
     * インスタンスを作成
     *
     * @memberof IntrinsicRatioVideos
     */
    constructor() {
        this.makeFit();

        let timeout = false;
        window.addEventListener('resize', () => {
            clearTimeout(timeout);
            timeout = setTimeout(this.handleResize.bind(this), 300);
        });
    }

    /**
     * 動画をコンテナにフィット
     *
     * @memberof IntrinsicRatioVideos
     */
    makeFit() {
        document.querySelectorAll('iframe, object, video').forEach((video) => {
            let ratio,
                iTargetWidth,
                container = video.parentNode;

            // 無視する動画をスキップします
            if (video.classList.contains('intrinsic-ignore') || video.parentNode.classList.contains('intrinsic-ignore')) {
                return true;
            }

            if (!video.dataset.origwidth) {
                // ビデオ要素の比率を取得する
                video.setAttribute('data-origwidth', video.width);
                video.setAttribute('data-origheight', video.height);
            }

            iTargetWidth = container.offsetWidth;

            // プロポーションから比率を取得する
            ratio = iTargetWidth / video.dataset.origwidth;

            // 比率に基づいてスケーリングし、比率を保持します
            video.style.width = iTargetWidth + 'px';
            video.style.height = video.dataset.origheight * ratio + 'px';
        });
    }

    /**
     * リサイズイベント
     *
     * @memberof IntrinsicRatioVideos
     */
    handleResize() {
        this.makeFit();
    }
}
