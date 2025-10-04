import { test, expect } from '@wordpress/e2e-test-utils-playwright';
import { addQueryArgs } from '@wordpress/url';

test.describe('admin menu', () => {
    test('go to admin', async ({ admin }) => {
        await admin.visitAdminPage('edit.php');
        await expect(admin.page.getByRole('heading', { name: 'ニュース', level: 1 })).toBeVisible();

        await admin.visitAdminPage(
            'edit.php',
            addQueryArgs('', {
                post_type: 'blog',
            }).slice(1)
        );
        await expect(admin.page.getByRole('heading', { name: 'ブログ', level: 1 })).toBeVisible();
    });
});
