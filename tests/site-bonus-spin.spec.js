const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'https://gbsale.ru';
const SCREENSHOTS = path.resolve(__dirname, 'screenshots');
const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ userAgent: USER_AGENT, viewport: { width: 1920, height: 1080 } });
  const page = await context.newPage();

  let failed = false;
  try {
    console.log('Logging in via signed URL...');
    const signedUrl = execSync('python tests/generate-e2e-url.py', {
      cwd: path.resolve(__dirname, '..'),
      encoding: 'utf8',
      timeout: 30000,
    }).trim();
    if (!signedUrl.startsWith('http')) throw new Error('unexpected url: ' + signedUrl);

    await page.goto(signedUrl, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForURL(`${BASE}/account/bonuses`, { timeout: 20000 });
    console.log('  ✓ Authenticated on /account/bonuses');

    const initialBalance = await page.$eval('.bonus-balance-value span', el => {
      return parseInt(el.textContent.replace(/\D/g, ''), 10);
    });
    console.log(`  ✓ Initial balance: ${initialBalance}`);

    const spinBtn = await page.$('button.roulette-btn-paid');
    if (!spinBtn) throw new Error('Paid spin button not found');
    const disabled = await spinBtn.evaluate(el => el.disabled);
    if (disabled) {
      console.log('  ⚠ Paid spin button is disabled (not enough bonuses or terms not accepted). Skipping spin.');
    } else {
      console.log('Clicking paid spin...');
      await spinBtn.click();

      // Balance should update almost immediately after the server responds,
      // well before the 5-second wheel animation finishes.
      await page.waitForFunction((start) => {
        const el = document.querySelector('.bonus-balance-value span');
        if (!el) return false;
        const current = parseInt(el.textContent.replace(/\D/g, ''), 10);
        return current !== start;
      }, initialBalance, { timeout: 2000 });

      const newBalance = await page.$eval('.bonus-balance-value span', el => {
        return parseInt(el.textContent.replace(/\D/g, ''), 10);
      });
      console.log(`  ✓ Balance updated immediately: ${initialBalance} -> ${newBalance}`);
    }

    await page.screenshot({ path: path.join(SCREENSHOTS, 'bonus-spin-balance.png'), fullPage: false });
    console.log('  ✓ Screenshot saved');
  } catch (e) {
    console.log('  ✗', e.message);
    failed = true;
    await page.screenshot({ path: path.join(SCREENSHOTS, 'bonus-spin-error.png'), fullPage: false });
  } finally {
    await browser.close();
  }

  process.exit(failed ? 1 : 0);
})();
