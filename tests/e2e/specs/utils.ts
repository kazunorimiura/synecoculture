import type { RequestUtils } from "@wordpress/e2e-test-utils-playwright";
import type { Page, BrowserContext } from '@playwright/test';

/**
 * 投稿を公開し、リクエストが完了したら（通知が表示されたら）解決する。
 * 
 * @link https://github.com/WordPress/gutenberg/blob/trunk/packages/e2e-test-utils-playwright/src/editor/publish-post.ts
 * 
 * @param page 
 */
export async function publishPost(page: Page): Promise<number|null> {
    await page.click( 'role=button[name="公開"i]' );
    const entitiesSaveButton = page.locator(
        'role=region[name="エディターの投稿パネル"i] >> role=button[name="保存"i]'
    );

    const isEntitiesSavePanelVisible = await entitiesSaveButton.isVisible();

    if ( isEntitiesSavePanelVisible ) {
        await entitiesSaveButton.click();
    }

    await page.click(
        'role=region[name="エディターの投稿パネル"i] >> role=button[name="公開"i]'
    );

    const urlString = await page
        .getByRole( 'region', { name: 'エディターの投稿パネル' } )
        .getByRole( 'textbox', { name: 'アドレス' } )
        .inputValue();
    const url = new URL( urlString );
    const postId = url.searchParams.get( 'p' );

    return typeof postId === 'string' ? parseInt( postId, 10 ) : null;
}

/**
 * 投稿を下書き保存し、リクエストが完了したら（通知が表示されたら）解決する。
 * 
 * @link https://github.com/WordPress/gutenberg/blob/trunk/packages/e2e-test-utils-playwright/src/editor/publish-post.ts
 * 
 * @param page 
 * @return
 */
export async function saveDraft(page: Page): Promise<number|null> {
    await page.locator('.editor-post-save-draft').waitFor();
    await page.click( '.editor-post-save-draft' );
    await page.locator('.editor-post-saved-state.is-saved').waitFor();
    
    const url = new URL( page.url() );
    const postId = url.searchParams.get( 'post' );

    return typeof postId === 'string' ? parseInt( postId, 10 ) : null;
}

/**
 * REST API を使用して特定の投稿を削除する。
 * 
 * @param requestUtils 
 * @param id 削除対象の投稿ID。
 */
export async function deletePost(requestUtils: RequestUtils, id: number) {
    await requestUtils.rest( {
        method: 'DELETE',
        path: `/wp/v2/posts/${ id }`,
        params: {
            force: true,
        },
    } );
}

/**
 * REST API を使用して特定のメディアを削除する。
 * 
 * @param requestUtils 
 * @param id 削除対象のメディアID。
 */
export async function deleteMedia(requestUtils: RequestUtils, id: number) {
    await requestUtils.rest( {
        method: 'DELETE',
        path: `/wp/v2/media/${ id }`,
        params: {
            force: true,
        },
    } );
}

/**
 * 編集した記事のプレビューページを開く。
 * 
 * @link https://github.com/WordPress/gutenberg/blob/trunk/packages/e2e-test-utils-playwright/src/editor/preview.ts
 *
 * @param page
 * @param context
 *
 * @return preview page.
 */
export async function openPreviewPage(page: Page, context: BrowserContext): Promise<Page> {
	const editorTopBar = page.locator(
		'role=region[name="エディタートップバー"]'
	);
	const previewButton = editorTopBar.locator(
		'role=button[name="プレビュー"]'
	);

	await previewButton.click();

	const [ previewPage ] = await Promise.all( [
		context.waitForEvent( 'page' ),
		page.click( 'role=menuitem[name="新しいタブでプレビュー"]' ),
	] );

	return previewPage;
}
