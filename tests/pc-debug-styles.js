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
    const rect = svg ? svg.querySelector('rect') : null;
    const g = svg ? svg.querySelector('g') : null;
    return {
      svgHtml: svg ? svg.outerHTML.slice(0, 300) : null,
      firstRectFill: rect ? rect.getAttribute('fill') : null,
      firstRectClass: rect ? rect.getAttribute('class') : null,
      firstRectStyle: rect ? window.getComputedStyle(rect).fill : null,
      firstGClass: g ? g.getAttribute('class') : null,
      firstGStyle: g ? window.getComputedStyle(g).fill : null,
    };
  });
  console.log(JSON.stringify(info, null, 2));

  await browser.close();
})();
