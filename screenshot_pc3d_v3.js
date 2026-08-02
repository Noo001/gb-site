const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1600, height: 1000 } });
  const page = await context.newPage();
  await page.goto('https://gbsale.ru/pc', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'C:/repos/gb-site/pc3dv3_case.png', fullPage: false });

  const steps = [
    { slot: 'case', selector: '[data-slot="case"]' },
    { slot: 'motherboard', selector: '[data-slot="motherboard"]' },
    { slot: 'cpu', selector: '[data-slot="cpu"]' },
    { slot: 'ram', selector: '[data-slot="ram"]' },
    { slot: 'gpu', selector: '[data-slot="gpu"]' },
  ];
  for (const s of steps) {
    await page.click(s.selector);
    await page.waitForTimeout(900);
    await page.screenshot({ path: `C:/repos/gb-site/pc3dv3_step_${s.slot}.png`, fullPage: false });
  }
  await browser.close();
  console.log('done');
})();
