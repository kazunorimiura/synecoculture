export default function clickableContainer() {
    document.querySelectorAll('.clickable-container').forEach((container) => {
        let isDragging = false;
        let startX, startY;

        // マウスダウン時の位置を記録
        container.addEventListener('mousedown', (e) => {
            startX = e.clientX;
            startY = e.clientY;
            isDragging = false;
        });

        // マウス移動を検知
        container.addEventListener('mousemove', (e) => {
            if (!startX) return;

            // 5px以上の移動があった場合はドラッグとみなす
            const moveX = Math.abs(e.clientX - startX);
            const moveY = Math.abs(e.clientY - startY);
            if (moveX > 5 || moveY > 5) {
                isDragging = true;
            }
        });

        // クリック（マウスアップ）時の処理
        container.addEventListener('mouseup', () => {
            // ドラッグ操作だった場合は遷移しない
            if (isDragging) {
                isDragging = false;
                startX = null;
                startY = null;
                return;
            }

            const url = container.dataset.clickableLink;
            if (url) {
                window.location.href = url;
            }

            // リセット
            isDragging = false;
            startX = null;
            startY = null;
        });

        // マウスが要素外に出た場合もリセット
        container.addEventListener('mouseleave', () => {
            isDragging = false;
            startX = null;
            startY = null;
        });
    });
}
