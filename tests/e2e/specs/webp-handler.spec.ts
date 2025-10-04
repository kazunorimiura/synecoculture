import path from 'path';
import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import type { Media } from '@wordpress/e2e-test-utils-playwright/build-types/request-utils/media';

test.describe('WPF Selective WebP Image Handler', () => {
    let uploadedMedia: Media | null = null;

    test.beforeAll(async ({ requestUtils }) => {
        await requestUtils.deleteAllMedia();
        uploadedMedia = await requestUtils.uploadMedia(path.resolve(process.cwd(), 'tests/e2e/assets/canola.jpg'));
    });

    test.afterAll(async ({ requestUtils }) => {
        await requestUtils.deleteAllMedia();
        await requestUtils.deleteAllPosts();
    });

    test.beforeEach(async ({ admin }) => {
        await admin.createNewPost();
    });

    test('メディアライブラリでWebPカラムが表示される', async ({ admin, page }) => {
        await admin.visitAdminPage('upload.php?mode=list');

        // WebPカラムヘッダーが存在することを確認
        const webpColumn = page.locator('th:has-text("WebP")').nth(0);
        await expect(webpColumn).toBeVisible();

        const testRow = page.locator(`tr[id="post-${uploadedMedia!.id}"]`);
        await expect(testRow).toBeVisible();

        const webpCell = testRow.locator('td.webp_status');
        await expect(webpCell).toBeVisible();

        // WebPステータス（生成済み または 未生成）が表示されることを確認
        const statusText = webpCell.locator('span');
        await expect(statusText).toBeVisible();
        await expect(statusText).toContainText(/生成済み|未生成/);
    });

    test('メディアライブラリでWebP生成・削除ボタンが動作する', async ({ admin, page }) => {
        await admin.visitAdminPage('upload.php?mode=list');

        const testRow = page.locator(`tr[id="post-${uploadedMedia!.id}"]`);
        const webpCell = testRow.locator('td.webp_status');

        // 生成ボタンまたは削除ボタンが存在することを確認
        const actionButton = webpCell.locator('button.button-link');
        await expect(actionButton).toBeVisible();

        // ボタンテキストが「生成」または「削除」であることを確認
        const buttonText = await actionButton.textContent();
        expect(buttonText).toMatch(/生成|削除/);

        // ボタンクリック前の状態を記録
        const initialStatusText = await webpCell.locator('span').textContent();

        // AJAX リクエストの完了を待つためのPromiseを設定
        // @ts-ignore
        const responsePromise = page.waitForResponse((response) => response.url().includes('admin-ajax.php') && response.request().postData()?.includes('toggle_webp_generation'));

        // ボタンをクリック
        await actionButton.click();

        // AJAX レスポンスを待つ
        const response = await responsePromise;
        expect(response.status()).toBe(200);

        // ページがリロードされるのを待つ
        await page.waitForLoadState('networkidle');

        // ボタンの状態が変更されたことを確認（リロード後）
        const updatedWebpCell = testRow.locator('td.webp_status');
        const updatedStatusText = await updatedWebpCell.locator('span').textContent();

        // ステータスが変更されていることを確認
        expect(updatedStatusText).not.toBe(initialStatusText);
    });

    test('一括操作でWebP生成・削除が実行できる', async ({ admin, page }) => {
        await admin.visitAdminPage('upload.php?mode=list');

        // 一括操作のドロップダウンを確認
        const bulkActionSelect = page.locator('#bulk-action-selector-top');
        await expect(bulkActionSelect).toBeVisible();

        // WebP生成とWebP削除オプションが存在することを確認
        const webpGenerateOption = bulkActionSelect.locator('option[value="generate_webp"]');
        const webpDeleteOption = bulkActionSelect.locator('option[value="delete_webp"]');

        await expect(webpGenerateOption).toHaveText('WebP生成');
        await expect(webpDeleteOption).toHaveText('WebP削除');

        const testRow = page.locator(`tr[id="post-${uploadedMedia!.id}"]`);
        const checkbox = testRow.locator('input[type="checkbox"]');
        await checkbox.check();

        // WebP生成を選択
        await bulkActionSelect.selectOption('generate_webp');

        // 適用ボタンをクリック
        const applyButton = page.locator('#doaction');

        // リダイレクトを待つ
        const navigationPromise = page.waitForNavigation();
        await applyButton.click();
        await navigationPromise;

        // 成功メッセージが表示されることを確認
        const successNotice = page.locator('.notice-success');
        await expect(successNotice).toBeVisible();
        await expect(successNotice).toContainText('WebPを生成しました');
    });

    test('添付ファイル編集画面でWebPメタボックスが表示される', async ({ admin, page }) => {
        // 添付ファイル編集画面に直接移動
        await admin.visitAdminPage(`post.php?post=${uploadedMedia!.id}&action=edit`);

        // WebP設定メタボックスが表示されることを確認
        const webpMetaBox = page.locator('#webp-options');
        await expect(webpMetaBox).toBeVisible();

        const webpTitle = webpMetaBox.locator('h2, .hndle');
        await expect(webpTitle).toContainText('WebP設定');

        // WebP生成チェックボックスが存在することを確認
        const webpCheckbox = webpMetaBox.locator('input[name="generate_webp"]');
        await expect(webpCheckbox).toBeVisible();

        // WebPファイルの状態表示を確認
        const statusElements = webpMetaBox.locator('p');
        const hasStatus = (await statusElements.count()) > 0;
        expect(hasStatus).toBe(true);

        // WebP生成/削除ボタンが存在することを確認
        const webpButtons = webpMetaBox.locator('button.button');
        if ((await webpButtons.count()) > 0) {
            const buttonText = await webpButtons.first().textContent();
            expect(buttonText).toMatch(/今すぐWebP生成|WebPファイルを削除/);
        }
    });

    test('WebPメタボックスでチェックボックス変更時の動作', async ({ admin, page }) => {
        await admin.visitAdminPage(`post.php?post=${uploadedMedia!.id}&action=edit`);

        const webpMetaBox = page.locator('#webp-options');
        const webpCheckbox = webpMetaBox.locator('input[name="generate_webp"]');

        // チェックボックスが存在することを確認
        await expect(webpCheckbox).toBeVisible();

        // チェックボックスの初期状態を記録
        const initialChecked = await webpCheckbox.isChecked();

        // チェックボックスの状態を変更
        if (initialChecked) {
            await webpCheckbox.uncheck();
        } else {
            await webpCheckbox.check();
        }

        // 更新ボタンをクリック
        const updateButton = page.locator('input[type="submit"][value="Update"]');
        await updateButton.click();

        // ページの更新を待つ
        await page.waitForLoadState('networkidle');

        // チェックボックスの状態が保存されたことを確認
        const updatedCheckbox = page.locator('#webp-options input[name="generate_webp"]');
        const updatedChecked = await updatedCheckbox.isChecked();
        expect(updatedChecked).toBe(!initialChecked);
    });

    test('WebP即座生成・削除ボタンの動作', async ({ admin, page }) => {
        await admin.visitAdminPage(`post.php?post=${uploadedMedia!.id}&action=edit`);

        const webpMetaBox = page.locator('#webp-options');
        const actionButton = webpMetaBox.locator('button.button');

        if ((await actionButton.count()) > 0) {
            const buttonText = await actionButton.textContent();

            // confirm → alert の2段階ダイアログを待つ
            const dialogPromise = page.waitForEvent('dialog').then(async (dialog) => {
                expect(dialog.type()).toBe('confirm');
                await dialog.accept();
                // alertが続けて出る場合も処理
                const nextDialog = await page.waitForEvent('dialog', { timeout: 3000 }).catch(() => null);
                if (nextDialog) {
                    expect(nextDialog.type()).toBe('alert');
                    await nextDialog.accept();
                }
            });

            // @ts-ignore
            const responsePromise = page.waitForResponse((response) => response.url().includes('admin-ajax.php') && response.request().postData()?.includes('toggle_webp_generation'));

            await Promise.all([dialogPromise, actionButton.click(), responsePromise, page.waitForLoadState('networkidle')]);

            // AJAX レスポンスを待つ
            const response = await responsePromise;
            expect(response.status()).toBe(200);

            // ページがリロードされるのを待つ
            await page.waitForLoadState('networkidle');

            // 処理後の状態変更を確認
            const updatedMetaBox = page.locator('#webp-options');
            const statusElements = updatedMetaBox.locator('p');
            await expect(statusElements.first()).toBeVisible();
        }
    });

    test('非対応画像形式でのメタボックス表示', async ({ admin, page }) => {
        // 非画像ファイル用の簡単なテキストファイルを作成
        const testTextBuffer = Buffer.from('This is a test file', 'utf-8');
        const testTextPath = path.join(__dirname, 'temp-test-file.txt');

        const fs = require('fs');
        fs.writeFileSync(testTextPath, testTextBuffer);

        try {
            // テキストファイルをアップロード
            await admin.visitAdminPage('media-new.php');

            const fileInput = page.locator('#async-upload');
            await fileInput.setInputFiles(testTextPath);

            // アップロードされたファイルのIDを取得
            let textFileId: string | null = null;
            const firstMediaRow = page.locator('tbody#the-list tr').first();
            if ((await firstMediaRow.count()) > 0) {
                const rowId = await firstMediaRow.getAttribute('id');
                if (rowId && rowId.startsWith('post-')) {
                    textFileId = rowId.replace('post-', '');
                }
            }

            if (textFileId) {
                // 編集画面に移動
                await admin.visitAdminPage(`post.php?post=${textFileId}&action=edit`);

                const webpMetaBox = page.locator('#webp-options');

                if ((await webpMetaBox.count()) > 0) {
                    // 非対応メッセージが表示されることを確認
                    const nonSupportedMessage = webpMetaBox.locator('p:has-text("この画像形式ではWebPを生成できません")');
                    await expect(nonSupportedMessage).toBeVisible();
                }
            }
        } finally {
            // テンポラリファイルを削除
            if (fs.existsSync(testTextPath)) {
                fs.unlinkSync(testTextPath);
            }
        }
    });

    test('ページWebPスキャナーの管理ページが表示される', async ({ admin, page }) => {
        // ページWebPスキャナーの管理ページに移動
        await admin.visitAdminPage('tools.php?page=page-webp-generator');

        // ページタイトルが正しく表示されることを確認
        const pageTitle = page.getByRole('heading', { name: 'ページ単位WebP生成・削除' });
        await expect(pageTitle).toContainText('ページ単位WebP生成・削除');

        // 基本的な入力要素が存在することを確認
        await expect(page.locator('#page_url')).toBeVisible();
        await expect(page.locator('#post_id')).toBeVisible();

        // スキャンボタンが存在することを確認
        const scanButton = page.locator('button:has-text("画像をスキャン")');
        await expect(scanButton).toBeVisible();
    });

    test('投稿IDによる画像スキャン機能', async ({ admin, page, editor }) => {
        // // まずテスト用の投稿を作成（現代的な方法）
        // await admin.createNewPost();

        // 投稿タイトルを設定
        await editor.canvas.locator('role=textbox[name="Add title"i]').fill('WebP Scanner Test Post');

        // 段落ブロックを追加
        await editor.insertBlock({ name: 'core/paragraph' });
        await page.keyboard.type('Test post content with image.');

        // 画像ブロックを追加
        await editor.insertBlock({ name: 'core/image' });

        // 既存のメディアライブラリから画像を選択
        await editor.canvas.locator('role=button[name="Media Library"i]').click();
        await page.waitForSelector('.media-modal', { state: 'visible' });

        // 特定の画像を選択
        const imageItem = page.locator(`[data-id="${uploadedMedia!.id}"]`);
        await imageItem.click();

        // 選択ボタンをクリック
        await page.click('role=button[name="Select"i]');
        await page.waitForSelector('.media-modal', { state: 'hidden' });

        // 投稿を保存
        const postId = await editor.publishPost();

        // ページWebPスキャナーに移動
        await admin.visitAdminPage('tools.php?page=page-webp-generator');

        // 投稿IDを入力
        // @ts-ignore
        await page.fill('#post_id', String(postId));

        // 投稿情報を取得ボタンをクリック
        const loadInfoButton = page.locator('button:has-text("投稿情報を取得")');
        await loadInfoButton.click();

        // 投稿情報が表示されるまで待つ
        await page.waitForSelector('#post_info', { state: 'visible', timeout: 5000 });

        // 投稿情報が正しく表示されることを確認
        const postInfo = page.locator('#post_info');
        await expect(postInfo).toContainText('WebP Scanner Test Post');
    });

    test('ページスキャン結果の表示と個別選択機能', async ({ admin, page, editor }) => {
        // テスト用の投稿を作成（画像付き）
        // await admin.createNewPost();

        // 投稿タイトルを設定
        await editor.canvas.locator('role=textbox[name="Add title"i]').fill('WebP Scanner Image Test Post');

        // 段落ブロックを追加
        await editor.insertBlock({ name: 'core/paragraph' });
        await page.keyboard.type('Test post with image.');

        // 画像ブロックを追加
        await editor.insertBlock({ name: 'core/image' });

        // 既存のメディアライブラリから画像を選択
        await editor.canvas.locator('role=button[name="Media Library"i]').click();
        await page.waitForSelector('.media-modal', { state: 'visible' });

        // 特定の画像を選択
        const imageItem = page.locator(`[data-id="${uploadedMedia!.id}"]`);
        await imageItem.click();

        // 選択ボタンをクリック
        await page.click('role=button[name="Select"i]');
        await page.waitForSelector('.media-modal', { state: 'hidden' });

        // 投稿を公開
        const postId = await editor.publishPost();

        // ページWebPスキャナーに移動
        await admin.visitAdminPage('tools.php?page=page-webp-generator');

        // 投稿IDを入力してスキャン実行
        // @ts-ignore
        await page.fill('#post_id', String(postId));

        // 投稿情報を取得ボタンをクリック
        const loadInfoButton = page.locator('role=button[name="投稿情報を取得"]');
        await loadInfoButton.click();

        // 画像スキャンボタンをクリック
        const scanButton = page.locator('button:has-text("画像をスキャン")');
        await scanButton.click();

        // スキャン結果が表示されるまで待つ
        await page.waitForSelector('#scan_results', { state: 'visible', timeout: 10000 });

        // スキャン結果の基本要素を確認
        const scanResults = page.locator('#scan_results');
        await expect(scanResults).toBeVisible();

        // スキャン結果にページ情報が表示されることを確認
        await expect(scanResults).toContainText('スキャンしたページ:');
        await expect(scanResults).toContainText('WebP Scanner Image Test Post');

        // 画像が見つかった場合の個別選択UI要素を確認
        const selectionControls = page.locator('.webp-selection-controls');
        if ((await selectionControls.count()) > 0) {
            // 「すべて選択」チェックボックスの確認
            const selectAllCheckbox = page.locator('#select_all_pending, #select_all_existing');
            if ((await selectAllCheckbox.count()) > 0) {
                await expect(selectAllCheckbox.first()).toBeVisible();
            }

            // 個別選択用チェックボックスの確認
            const imageCheckboxes = page.locator('.webp-image-checkbox input[type="checkbox"]');
            if ((await imageCheckboxes.count()) > 0) {
                await expect(imageCheckboxes.first()).toBeVisible();
            }

            // 選択ボタンの確認
            const actionButtons = page.locator('#generate_selected_btn, #delete_selected_btn');
            if ((await actionButtons.count()) > 0) {
                await expect(actionButtons.first()).toBeVisible();
            }
        }
    });

    test('個別選択でのWebP生成機能', async ({ admin, page, editor }) => {
        // 画像付きの投稿を作成
        // await admin.createNewPost();

        // 投稿タイトルを設定
        await editor.canvas.locator('role=textbox[name="Add title"i]').fill('WebP Individual Selection Test');

        // 段落ブロックを追加
        await editor.insertBlock({ name: 'core/paragraph' });
        await page.keyboard.type('Test post with image.');

        // 画像ブロックを追加
        await editor.insertBlock({ name: 'core/image' });

        // ファイルをアップロードするか、既存の画像を選択
        await editor.canvas.locator('role=button[name="Media Library"i]').click();
        await page.waitForSelector('.media-modal', { state: 'visible' });

        // 特定の画像を選択
        const imageItem = page.locator(`[data-id="${uploadedMedia!.id}"]`);
        await imageItem.click();

        // 選択ボタンをクリック
        await page.click('role=button[name="Select"i]');
        await page.waitForSelector('.media-modal', { state: 'hidden' });

        // 投稿を公開
        const postId = await editor.publishPost();

        // ページWebPスキャナーに移動
        await admin.visitAdminPage('tools.php?page=page-webp-generator');

        // 投稿IDを入力してスキャン実行
        // @ts-ignore
        await page.fill('#post_id', String(postId));

        // 投稿情報を取得ボタンをクリック
        const loadInfoButton = page.locator('role=button[name="投稿情報を取得"]');
        await loadInfoButton.click();

        // 画像スキャンボタンをクリック
        const scanButton = page.locator('button:has-text("画像をスキャン")');
        await scanButton.click();

        // スキャン結果が表示されるまで待つ
        await page.waitForSelector('#scan_results', { state: 'visible', timeout: 10000 });

        // 未生成の画像リストが表示されるまで待つ
        const pendingImagesGrid = page.locator('#pending_images_grid');
        if ((await pendingImagesGrid.count()) > 0) {
            // 最初の画像にチェックを入れる
            const firstImageCheckbox = pendingImagesGrid.locator('input[type="checkbox"][id^="pending_"]').first();
            await firstImageCheckbox.check();

            // 選択された画像数の表示を確認
            const selectedCount = page.locator('#pending_selected_count');
            await expect(selectedCount).toContainText('1');

            // 確認ダイアログを処理
            page.once('dialog', async (dialog) => {
                expect(dialog.type()).toBe('confirm');
                await dialog.accept();
            });

            // 生成ボタンが有効になることを確認
            const generateButton = page.locator('#generate_selected_btn');
            await expect(generateButton).toBeEnabled();

            // WebP生成を実行
            // @ts-ignore
            page.waitForResponse((response) => response.url().includes('admin-ajax.php') && response.request().postData()?.includes('generate_selected_webp'));

            await generateButton.click();

            // 進捗表示が表示されることを確認
            await page.waitForSelector('#generation_progress', { state: 'visible', timeout: 5000 });

            // 進捗バーの確認
            const progressBar = page.locator('#progress_fill');
            await expect(progressBar).toBeVisible();

            // ログエリアの確認
            const logArea = page.locator('#generation_log');
            await expect(logArea).toBeVisible();

            // 処理完了を待つ（タイムアウトを長めに設定）
            await page.waitForFunction(
                () => {
                    const progressText = document.querySelector('#progress_text');
                    return progressText && progressText.textContent?.includes('完了しました');
                },
                { timeout: 30000 },
            );
        }
    });

    test('全選択機能のテスト', async ({ admin, page, editor }) => {
        // 複数画像付きの投稿を作成
        // await admin.createNewPost();

        // 投稿タイトルを設定
        await editor.canvas.locator('role=textbox[name="Add title"i]').fill('WebP Select All Test');

        // 段落ブロックを追加
        await editor.insertBlock({ name: 'core/paragraph' });
        await page.keyboard.type('Test post with multiple images.');

        // 最初の画像ブロックを追加
        await editor.insertBlock({ name: 'core/image' });

        // 既存のメディアライブラリから画像を選択
        await editor.canvas.locator('role=button[name="Media Library"i]').click();
        await page.waitForSelector('.media-modal', { state: 'visible' });

        // 特定の画像を選択
        const imageItem = page.locator(`[data-id="${uploadedMedia!.id}"]`);
        await imageItem.click();

        // 選択ボタンをクリック
        await page.click('role=button[name="Select"i]');
        await page.waitForSelector('.media-modal', { state: 'hidden' });

        // 段落ブロックを追加
        await editor.insertBlock({ name: 'core/paragraph' });
        await page.keyboard.type('More content...');

        // 2番目の画像ブロックを追加
        await editor.insertBlock({ name: 'core/image' });

        // 既存のメディアライブラリから画像を選択
        await editor.canvas.locator('role=button[name="Media Library"i]').click();
        await page.waitForSelector('.media-modal', { state: 'visible' });

        // 特定の画像を選択
        await imageItem.click();

        // 選択ボタンをクリック
        await page.click('role=button[name="Select"i]');
        await page.waitForSelector('.media-modal', { state: 'hidden' });

        // 投稿を公開
        const postId = await editor.publishPost();

        // ページWebPスキャナーに移動
        await admin.visitAdminPage('tools.php?page=page-webp-generator');

        // 投稿IDを入力してスキャン実行
        // @ts-ignore
        await page.fill('#post_id', String(postId));

        // 投稿情報を取得ボタンをクリック
        const loadInfoButton = page.locator('role=button[name="投稿情報を取得"]');
        await loadInfoButton.click();

        // 画像スキャンボタンをクリック
        const scanButton = page.locator('button:has-text("画像をスキャン")');
        await scanButton.click();

        // スキャン結果が表示されるまで待つ
        await page.waitForSelector('#scan_results', { state: 'visible', timeout: 10000 });

        // 未生成の画像リストが表示されるまで待つ
        const pendingImagesGrid = page.locator('#pending_images_grid');
        if ((await pendingImagesGrid.count()) > 0) {
            const imageCheckboxes = pendingImagesGrid.locator('input[type="checkbox"]');
            const checkboxCount = await imageCheckboxes.count();

            if (checkboxCount > 0) {
                // 「すべて選択」チェックボックスをクリック
                const selectAllCheckbox = page.locator('#select_all_pending');
                await selectAllCheckbox.check();

                // 全ての個別チェックボックスがチェックされることを確認
                for (let i = 0; i < checkboxCount; i++) {
                    const checkbox = imageCheckboxes.nth(i);
                    await expect(checkbox).toBeChecked();
                }

                // 選択数表示の確認
                const selectedCount = page.locator('#pending_selected_count');
                await expect(selectedCount).toContainText(checkboxCount.toString());

                // 「すべて選択」を解除
                await selectAllCheckbox.uncheck();

                // 全ての個別チェックボックスが解除されることを確認
                for (let i = 0; i < checkboxCount; i++) {
                    const checkbox = imageCheckboxes.nth(i);
                    await expect(checkbox).not.toBeChecked();
                }

                // 選択数が0になることを確認
                await expect(selectedCount).toContainText('0');
            }
        }
    });

    test('エラーハンドリングのテスト', async ({ admin, page }) => {
        await admin.visitAdminPage('tools.php?page=page-webp-generator');

        // JavaScriptエラーを監視
        const jsErrors: string[] = [];
        page.on('pageerror', (error) => {
            jsErrors.push(error.message);
        });

        // コンソールエラーを監視
        const consoleErrors: string[] = [];
        page.on('console', (msg) => {
            if (msg.type() === 'error') {
                consoleErrors.push(msg.text());
            }
        });

        // 基本的な画面要素が表示されていることを確認
        const pageTitle = page.getByRole('heading', { name: 'ページ単位WebP生成・削除' });
        await expect(pageTitle).toContainText('ページ単位WebP生成・削除');

        // 無効な投稿IDでのスキャンをテスト
        await page.fill('#post_id', '99999');
        const scanButton = page.locator('button:has-text("画像をスキャン")');
        await scanButton.click();

        // エラーが表示されることを確認（アラートダイアログ）
        page.once('dialog', async (dialog) => {
            expect(dialog.type()).toBe('alert');
            expect(dialog.message()).toContain('失敗');
            await dialog.accept();
        });

        // 少し待ってからエラーをチェック
        await page.waitForTimeout(2000);

        // 致命的なJavaScriptエラーが発生していないことを確認
        expect(jsErrors.length).toBe(0);

        // 重大なコンソールエラーが発生していないことを確認
        const criticalErrors = consoleErrors.filter((error) => error.includes('TypeError') || error.includes('ReferenceError'));
        expect(criticalErrors.length).toBe(0);
    });
});
