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
    const svg = document.querySelector('.pc-case-svg');
    if (svg) {
      svg.style.background = 'red';
      svg.style.opacity = '1';
      const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
      rect.setAttribute('x', '0');
      rect.setAttribute('y', '0');
      rect.setAttribute('width', '420');
      rect.setAttribute('height', '620');
      rect.setAttribute('fill', 'yellow');
      svg.insertBefore(rect, svg.firstChild);
    }
  });
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'tests/screenshots/pc-debug-svg-visible.png' });

  await browser.close();
})();
