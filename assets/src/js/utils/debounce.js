export function debounce(func, delay) {
    let timeoutId;

    return function (...args) {
        // 以前のタイムアウトをクリア
        if (timeoutId) {
            clearTimeout(timeoutId);
        }

        // 新しいタイムアウトを設定
        timeoutId = setTimeout(() => {
            func.apply(this, args);
        }, delay);
    };
}
