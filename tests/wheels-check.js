const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'https://gbsale.ru';
const SCREENSHOTS = path.resolve(__dirname, 'screenshots', 'wheels-check');
const fs = require('fs');
if (!fs.existsSync(SCREENSHOTS)) fs.mkdirSync(SCREENSHOTS, { recursive: true });

const wait = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  const signedUrl = execSync('python tests/generate-e2e-url.py', {
    cwd: path.resolve(__dirname, '..'),
    encoding: 'utf8',
    timeout: 30000,
  }).trim();

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    viewport: { width: 1280, height: 900 }
  });
  const page = await context.newPage();
  page.on('console', msg => { if (msg.type() === 'error') console.log('PAGE ERROR:', msg.text()); });
  page.on('pageerror', err => console.log('JS ERROR:', err.message));

  try {
    console.log('Opening signed URL...');
    await page.goto(signedUrl, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForURL(`${BASE}/account/bonuses`, { timeout: 20000 });
    console.log('Authenticated on /account/bonuses');

    const shot = async (name) => {
      await page.screenshot({ path: path.join(SCREENSHOTS, `${name}.png`), fullPage: true });
      console.log('  Screenshot', name);
    };

    const balance = await page.$eval('.bonus-balance-value span', el => parseInt(el.textContent.replace(/\D/g, ''), 10));
    console.log('Balance:', balance);

    // Pole Chudes
    console.log('\nPole Chudes 3D');
    await page.click('.wheel-tab:has-text("Поле чудес 3D")');
    await wait(1000);
    await shot('pole-initial');

    if (balance >= 1) {
      await page.click('button.roulette-btn-paid');
      console.log('  Spinning...');
      await wait(18500);
      await shot('pole-result');
      const msg = await page.$eval('.roulette-message', el => el.textContent).catch(() => '');
      console.log('  Result:', msg);
    } else {
      console.log('  Not enough balance');
    }

    // Marketplace
    console.log('\nMarketplace');
    const tabs = await page.locator('button.wheel-tab').all();
    const tabTexts = await Promise.all(tabs.map(t => t.textContent()));
    let idx = tabTexts.findIndex(t => t.includes('Маркетплейс'));
    await tabs[idx].click();
    await wait(1000);
    await shot('market-initial');

    await page.click('button.roulette-btn-paid');
    console.log('  Spinning...');
    await wait(8000);
    await shot('market-result');
    const marketMsg = await page.$eval('.roulette-message', el => el.textContent).catch(() => '');
    console.log('  Result:', marketMsg);

    // Neon
    console.log('\nNeon Fusion');
    idx = tabTexts.findIndex(t => t.includes('Neon Fusion'));
    await tabs[idx].click();
    await wait(1000);
    await shot('neon-initial');

    await page.click('button.roulette-btn-paid');
    console.log('  Spinning...');
    await wait(7000);
    await shot('neon-result');
    const neonMsg = await page.$eval('.roulette-message', el => el.textContent).catch(() => '');
    console.log('  Result:', neonMsg);

    console.log('\nAll variants checked');
  } catch (e) {
    console.error('ERROR:', e.message);
    await page.screenshot({ path: path.join(SCREENSHOTS, 'error.png'), fullPage: true });
    process.exit(1);
  } finally {
    await browser.close();
  }
})();
