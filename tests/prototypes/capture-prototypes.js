const { chromium } = require('playwright-core');
const path = require('path');

(async () => {
  const browser = await chromium.launch({ headless: true });
  const page = await browser.newPage({ viewport: { width: 1280, height: 900 } });

  const files = [
    'wheel-marketplace.html',
    'wheel-field-of-wonders.html',
    'wheel-neon.html',
  ];

  for (const file of files) {
    const filePath = path.resolve(__dirname, file);
    await page.goto('file://' + filePath);
    await page.waitForTimeout(1200);
    const out = path.resolve(__dirname, file.replace('.html', '.png'));
    await page.screenshot({ path: out, fullPage: true });
    console.log('Screenshot:', out);

    // Also capture a mid-spin frame
    await page.click('button.spin');
    await page.waitForTimeout(1500);
    await page.screenshot({ path: path.resolve(__dirname, file.replace('.html', '-spin.png')), fullPage: true });
    console.log('Screenshot spinning:', path.resolve(__dirname, file.replace('.html', '-spin.png')));
  }

  await browser.close();
})();
