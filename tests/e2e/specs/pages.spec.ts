import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.beforeEach(async ({ page }) => {
    await page.goto('/');
});

test.describe('pages', () => {
    test('home', async ({ page }) => {
        await expect(page).toHaveTitle(/EnterpriseWP/);
        await expect(page).toHaveScreenshot();
    });
});
