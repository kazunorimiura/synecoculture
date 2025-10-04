/**
 * External dependencies
 */
import { fileURLToPath } from 'url';
import { defineConfig } from '@playwright/test';

/**
 * Read environment variables from file.
 * https://github.com/motdotla/dotenv
 */
require('dotenv').config();

/**
 * WordPress dependencies
 */
const baseConfig = require('@wordpress/scripts/config/playwright.config.js');

const config = defineConfig({
    ...baseConfig,
    reporter: process.env.CI ? [['github'], ['./config/flaky-tests-reporter.ts']] : 'list',
    workers: 1,
    globalSetup: fileURLToPath(new URL('./config/global-setup.ts', 'file:' + __filename).href),
    use: {
        baseURL: process.env.WP_BASE_URL,
        storageState: process.env.STORAGE_STATE_PATH,
        video: 'retain-on-failure',
    },
});

export default config;
