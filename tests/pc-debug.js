const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch();
  const context = await browser.newContext({
    httpCredentials: { username: 'admin@gbsale.ru', password: 'GB-admin-2026-x7Q' },
    extraHTTPHeaders: { 'Cache-Control': 'no-cache' }
  });
  const page = await context.newPage();
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto('https://gbsale.ru/pc', { waitUntil: 'networkidle' });
  await page.waitForTimeout(2000);

  // Remove 3D transform to see if SVG renders
  await page.evaluate(() => {
    const el = document.querySelector('.pc-case-3d');
    if (el) el.style.transform = 'none';
  });
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'tests/screenshots/pc-debug-no-transform.png' });

  // Restore transform and screenshot with front face hidden
  await page.evaluate(() => {
    const el = document.querySelector('.pc-case-3d');
    if (el) el.style.transform = '';
  });
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'tests/screenshots/pc-debug-transform.png' });

  await browser.close();
})();
