const { chromium } = require('playwright');
const path = require('path');

const BASE = 'https://gbsale.ru';
const EMAIL = 'e2e-wheel@gbsale.ru';
const PASSWORD = 'e2e-test-password';
const SCREENSHOTS = path.resolve(__dirname, 'screenshots', 'wheels-check');
const fs = require('fs');
if (!fs.existsSync(SCREENSHOTS)) fs.mkdirSync(SCREENSHOTS, { recursive: true });

const wait = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
    viewport: { width: 1280, height: 900 }
  });
  const page = await context.newPage();
  page.on('console', msg => { if (msg.type() === 'error') console.log('PAGE ERROR:', msg.text()); });
  page.on('pageerror', err => console.log('JS ERROR:', err.message));

  try {
    console.log('Logging in...');
    await page.goto(`${BASE}/login`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.fill('input[name="email"]', EMAIL);
    await page.fill('input[name="password"]', PASSWORD);
    await Promise.all([
      page.waitForURL(`${BASE}/account`, { timeout: 20000 }),
      page.click('form button[type="submit"]')
    ]);
    console.log('Logged in');

    await page.goto(`${BASE}/account/bonuses`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('.bonus-page', { timeout: 15000 });
    console.log('Bonus page loaded');

    const shot = async (name) => {
      await page.screenshot({ path: path.join(SCREENSHOTS, `${name}.png`), fullPage: true });
      console.log('  Screenshot', name);
    };

    // Top up balance if needed
    const balance = await page.$eval('.bonus-balance-value span', el => parseInt(el.textContent.replace(/\D/g, ''), 10));
    console.log('Balance:', balance);

    // Pole Chudes
    console.log('\nPole Chudes 3D');
    await page.click('.wheel-tab:has-text("Поле чудес 3D")');
    await wait(800);
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
    await wait(800);
    await shot('neon-initial');

    await page.click('button.roulette-btn-paid');
    console.log('  Spinning...');
    await wait(6500);
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
