const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();
  await page.goto('https://gbsale.ru/pc', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(2000);
  await page.screenshot({ path: 'pc3dv2_case.png', fullPage: false });
  
  const steps = ['Процессор', 'Материнская плата', 'Память', 'Блок питания'];
  for (let i = 0; i < steps.length; i++) {
    await page.click(`.pc-step-head:has-text("${steps[i]}")`, { timeout: 5000 });
    await page.waitForTimeout(1500);
    await page.screenshot({ path: `pc3dv2_step_${String(i + 2).padStart(2, '0')}.png`, fullPage: false });
  }
  
  await browser.close();
  console.log('done');
})();
