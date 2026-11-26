import { defineConfig } from '@playwright/test';

export default defineConfig({
  testDir: './tests/layout',
  fullyParallel: true,
  forbidOnly: !!process.env.CI,
  retries: 0,
  workers: process.env.CI ? 2 : undefined,
  reporter: [['list'], ['html', { open: 'never' }]],
  use: {
    baseURL: 'http://127.0.0.1:4173',
    browserName: 'chromium',
    screenshot: 'only-on-failure',
    trace: 'retain-on-failure',
  },
  webServer: {
    command: 'php -S 127.0.0.1:4173 tests/layout-router.php',
    url: 'http://127.0.0.1:4173',
    reuseExistingServer: false,
  },
});
