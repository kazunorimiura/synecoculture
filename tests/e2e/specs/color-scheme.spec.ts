import { test, expect } from '@wordpress/e2e-test-utils-playwright';

test.beforeEach(async ({ page }) => {
    await page.goto('/');
});

test.describe('color scheme', () => {
    test.use({ colorScheme: 'dark' });

    test('dark', async ({ page }) => {
        const html = page.locator('html');
        await expect(html).toHaveAttribute('data-site-theme', 'dark');
        await expect(page).toHaveScreenshot();
    });
});

test.describe('color scheme', () => {
    test.use({ colorScheme: 'light' });

    test('light', async ({ page }) => {
        const html = page.locator('html');
        await expect(html).toHaveAttribute('data-site-theme', 'light');
        await expect(page).toHaveScreenshot();
    });
});
