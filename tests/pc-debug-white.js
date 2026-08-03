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

  await page.evaluate(() => {
    const front = document.querySelector('.pc-case-face--front');
    if (front) {
      front.style.background = 'white';
      front.style.border = '3px solid red';
    }
    const faces = document.querySelectorAll('.pc-case-face--back, .pc-case-face--top, .pc-case-face--bottom, .pc-case-face--left, .pc-case-face--right');
    faces.forEach(f => f.style.display = 'none');
    const svg = document.querySelector('.pc-case-svg');
    if (svg) {
      svg.style.background = 'lime';
      svg.style.opacity = '1';
    }
  });
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'tests/screenshots/pc-debug-white-front.png' });

  await browser.close();
})();
