import { test, expect } from '@playwright/test';

async function loadPage(page) {
  const response = await page.goto('/');
  expect(response.status()).toBe(200);
  await page.locator('.logo').evaluate(image => image.decode());
  await expect(page.locator('a[href="tel:+576024454545"]')).toBeVisible();
  await expect(page.locator('a[href="mailto:elcrisol.cali@gmail.com"]')).toBeVisible();
  await expect(page.locator('a[href*="google.com/maps/search/"]')).toBeVisible();
}

const viewports = [
  { width: 320, height: 480 },
  { width: 320, height: 568 },
  { width: 360, height: 640 },
  { width: 375, height: 667 },
  { width: 390, height: 664 },
  { width: 768, height: 1024 },
  { width: 1280, height: 800 },
];

for (const viewport of viewports) {
  test(`all maintenance content fits at ${viewport.width}x${viewport.height}`, async ({ page }, testInfo) => {
    await page.setViewportSize(viewport);
    await loadPage(page);
    const layout = await page.evaluate(() => ({
      contentHeight: document.scrollingElement.scrollHeight,
      contentWidth: document.scrollingElement.scrollWidth,
      viewportHeight: window.innerHeight,
      viewportWidth: window.innerWidth,
      mainTop: document.querySelector('main').getBoundingClientRect().top,
      mainBottom: document.querySelector('main').getBoundingClientRect().bottom,
      fontSize: parseFloat(getComputedStyle(document.body).fontSize),
    }));
    console.log(`${viewport.width}x${viewport.height}: content height ${layout.contentHeight}, viewport ${layout.viewportHeight}`);
    await testInfo.attach('full-page', { body: await page.screenshot({ fullPage: true }), contentType: 'image/png' });
    expect(layout.contentHeight, 'No vertical scrolling at the default text size').toBeLessThanOrEqual(layout.viewportHeight + 1);
    expect(layout.contentWidth, 'No horizontal scrolling').toBeLessThanOrEqual(layout.viewportWidth);
    expect(layout.mainTop).toBeGreaterThanOrEqual(0);
    expect(layout.mainBottom).toBeLessThanOrEqual(layout.viewportHeight + 1);
    expect(layout.fontSize, 'Keep the main copy readable').toBeGreaterThanOrEqual(16);
    await expect(page.locator('.thanks')).toBeInViewport({ ratio: 1 });
  });
}

for (const enlargedText of [false, true]) {
  test(`very short screens keep every contact reachable${enlargedText ? ' with 200% text' : ''}`, async ({ page }) => {
    await page.setViewportSize({ width: 320, height: 320 });
    await loadPage(page);
    if (enlargedText) await page.addStyleTag({ content: ':root { font-size: 200% !important; }' });
    const layout = await page.evaluate(() => ({
      top: document.querySelector('main').getBoundingClientRect().top,
      height: document.scrollingElement.scrollHeight,
      width: document.scrollingElement.scrollWidth,
      overflow: [document.documentElement, document.body].map(element => getComputedStyle(element).overflowY),
    }));
    expect(layout.top, 'Do not center oversized content above the visible area').toBeGreaterThanOrEqual(0);
    expect(layout.height).toBeGreaterThan(320);
    expect(layout.width).toBeLessThanOrEqual(320);
    expect(layout.overflow).not.toContain('hidden');
    expect(layout.overflow).not.toContain('clip');
    for (const selector of ['.logo', 'h1', 'a[href^="tel:"]', '.email', 'address p a', '.thanks']) {
      const element = page.locator(selector);
      await element.scrollIntoViewIfNeeded();
      await expect(element).toBeInViewport();
    }
    await page.locator('.email').focus();
    await expect(page.locator('.email')).toBeFocused();
  });
}
