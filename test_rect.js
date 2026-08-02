const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();
  await page.goto('https://gbsale.ru/pc', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(2000);
  
  await page.evaluate(() => {
    const svg = document.querySelector('.pc-case-svg');
    if (svg) {
      const rect = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
      rect.setAttribute('x', '150');
      rect.setAttribute('y', '150');
      rect.setAttribute('width', '100');
      rect.setAttribute('height', '100');
      rect.setAttribute('fill', 'red');
      rect.setAttribute('stroke', 'black');
      rect.setAttribute('stroke-width', '3');
      svg.appendChild(rect);
    }
  });
  
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'pc3d_test_rect.png', fullPage: false });
  await browser.close();
  console.log('done');
})();
