/* global wp */

wp.domReady(function () {
    // ボタンからsquaredバリエーションを削除
    wp.blocks.unregisterBlockStyle('core/button', 'squared');

    // 区切りブロックからスタイルバリエーションを削除
    wp.blocks.unregisterBlockStyle('core/separator', 'wide');
    wp.blocks.unregisterBlockStyle('core/separator', 'dots');

    // 段落ブロックにスタイルバリエーションを追加
    wp.blocks.registerBlockStyle('core/paragraph', {
        name: 'notice--info',
        label: '注釈（情報）',
    });
    wp.blocks.registerBlockStyle('core/paragraph', {
        name: 'notice--positive',
        label: '注釈（ポジティブ）',
    });
    wp.blocks.registerBlockStyle('core/paragraph', {
        name: 'notice--negative',
        label: '注釈（ネガティブ）',
    });
    wp.blocks.registerBlockStyle('core/paragraph', {
        name: 'notice--warning',
        label: '注釈（警告）',
    });
    wp.blocks.registerBlockStyle('core/paragraph', {
        name: 'notice--info--bold',
        label: '強調注釈（情報）',
    });
    wp.blocks.registerBlockStyle('core/paragraph', {
        name: 'notice--positive--bold',
        label: '強調注釈（ポジティブ）',
    });
    wp.blocks.registerBlockStyle('core/paragraph', {
        name: 'notice--negative--bold',
        label: '強調注釈（ネガティブ）',
    });
    wp.blocks.registerBlockStyle('core/paragraph', {
        name: 'notice--warning--bold',
        label: '強調注釈（警告）',
    });
});
