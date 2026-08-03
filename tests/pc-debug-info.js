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
    const front = document.querySelector('.pc-case-face--front');
    const case3d = document.querySelector('.pc-case-3d');
    return {
      svgExists: !!svg,
      frontExists: !!front,
      case3dExists: !!case3d,
      svgRect: svg ? svg.getBoundingClientRect() : null,
      frontRect: front ? front.getBoundingClientRect() : null,
      case3dRect: case3d ? case3d.getBoundingClientRect() : null,
      svgComputed: svg ? window.getComputedStyle(svg) : null,
      frontComputed: front ? window.getComputedStyle(front) : null,
      svgChildren: svg ? svg.childElementCount : 0
    };
  });
  console.log(JSON.stringify(info, null, 2));

  await browser.close();
})();
