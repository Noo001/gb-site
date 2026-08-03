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

  const info = await page.evaluate(() => {
    const svg = document.querySelector('.pc-case-svg');
    const rect = svg ? svg.querySelector('rect.pc-case-body') : null;
    const interior = svg ? svg.querySelector('rect.pc-case-interior') : null;
    const front = document.querySelector('.pc-case-face--front');
    return {
      rectFill: rect ? window.getComputedStyle(rect).fill : null,
      rectStroke: rect ? window.getComputedStyle(rect).stroke : null,
      rectBounding: rect ? rect.getBoundingClientRect() : null,
      interiorFill: interior ? window.getComputedStyle(interior).fill : null,
      interiorBounding: interior ? interior.getBoundingClientRect() : null,
      frontBounding: front ? front.getBoundingClientRect() : null,
    };
  });
  console.log(JSON.stringify(info, null, 2));

  await browser.close();
})();
