const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'https://gbsale.ru';
const SCREENSHOTS = path.resolve(__dirname, 'screenshots', 'bonus-wheels');
const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

const wait = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  const signedUrl = execSync('python tests/generate-e2e-url.py', {
    cwd: path.resolve(__dirname, '..'),
    encoding: 'utf8',
    timeout: 30000,
  }).trim();
  if (!signedUrl.startsWith('http')) throw new Error('unexpected url: ' + signedUrl);

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ userAgent: USER_AGENT, viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();

  try {
    console.log('Opening /account/bonuses...');
    await page.goto(signedUrl, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForURL(`${BASE}/account/bonuses`, { timeout: 20000 });
    await page.waitForSelector('.bonus-page', { timeout: 15000 });
    await wait(1500);

    const variants = [
      { key: 'pole', name: 'Поле чудес 3D', selector: '.wheel-tab:has-text("Поле чудес 3D")', wheel: '.pc-wheel-stage' },
      { key: 'market', name: 'Маркетплейс', selector: '.wheel-tab:has-text("Маркетплейс")', wheel: '.rp-stage' },
      { key: 'neon', name: 'Neon Fusion', selector: '.wheel-tab:has-text("Neon Fusion")', wheel: '.neon-wheel-stage' },
    ];

    for (const v of variants) {
      console.log(`\nChecking ${v.name}...`);
      await page.click(v.selector);
      await wait(1200);
      await page.waitForSelector(v.wheel, { timeout: 10000 });
      await page.screenshot({ path: path.join(SCREENSHOTS, `${v.key}-static.png`), fullPage: false });
      console.log(`  screenshot ${v.key}-static.png`);
    }

    // Test spins on each variant (low balance is fine, just verify animation starts)
    for (const v of variants) {
      console.log(`\nSpinning ${v.name}...`);
      await page.click(v.selector);
      await wait(800);
      const btn = await page.$('.roulette-btn-paid');
      if (!btn) {
        console.log('  button not found');
        continue;
      }
      const disabled = await btn.evaluate(el => el.disabled);
      if (disabled) {
        console.log('  button disabled, skipping spin');
        continue;
      }
      await btn.click();
      await wait(1500);
      await page.screenshot({ path: path.join(SCREENSHOTS, `${v.key}-spinning.png`), fullPage: false });
      console.log(`  screenshot ${v.key}-spinning.png`);
      await wait(6000);
      await page.screenshot({ path: path.join(SCREENSHOTS, `${v.key}-result.png`), fullPage: false });
      console.log(`  screenshot ${v.key}-result.png`);
    }

    console.log('\nDone');
  } catch (e) {
    console.error('ERROR:', e.message);
    await page.screenshot({ path: path.join(SCREENSHOTS, 'error.png'), fullPage: false });
    process.exit(1);
  } finally {
    await browser.close();
  }
})();
