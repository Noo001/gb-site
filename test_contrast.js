const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();
  await page.goto('https://gbsale.ru/pc', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(2000);
  
  // Add high-contrast test styles
  await page.addStyleTag({
    content: `
      .pc-slot .pc-shape { fill: transparent !important; stroke: #000 !important; stroke-width: 3 !important; }
      .pc-slot .pc-case-body { fill: #fff !important; stroke: #000 !important; stroke-width: 4 !important; }
      .pc-detail { fill: #ff0000 !important; stroke: #000 !important; stroke-width: 2 !important; }
      .pc-detail-line { stroke: #0000ff !important; stroke-width: 3 !important; }
      .pc-trace { stroke: #00ff00 !important; stroke-width: 3 !important; }
      .pc-glass { display: none !important; }
    `
  });
  
  await page.waitForTimeout(500);
  await page.screenshot({ path: 'pc3d_test_contrast.png', fullPage: false });
  await browser.close();
  console.log('done');
})();
