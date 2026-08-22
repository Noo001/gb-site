const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');
const fs = require('fs');

const BASE = 'https://gbsale.ru';
const SCREENSHOTS = path.resolve(__dirname, 'screenshots', 'bonus-wheel-market');
const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

const wait = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  if (!fs.existsSync(SCREENSHOTS)) fs.mkdirSync(SCREENSHOTS, { recursive: true });

  const signedUrl = execSync('python tests/generate-e2e-url.py', {
    cwd: path.resolve(__dirname, '..'),
    encoding: 'utf8',
    timeout: 30000,
  }).trim();
  if (!signedUrl.startsWith('http')) throw new Error('unexpected url: ' + signedUrl);

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    userAgent: USER_AGENT,
    viewport: { width: 1440, height: 900 },
    extraHTTPHeaders: { 'Cache-Control': 'no-cache' },
    recordVideo: { dir: SCREENSHOTS, size: { width: 1092, height: 595 } },
  });
  const page = await context.newPage();

  try {
    console.log('Opening /account/bonuses...');
    await page.goto(signedUrl, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForURL(`${BASE}/account/bonuses`, { timeout: 20000 });
    await page.waitForSelector('.bonus-page', { timeout: 15000 });
    await wait(1200);

    // Initial state: check that cards are visible on both sides of the pointer
    const initialCardsCheck = await page.evaluate(() => {
      const cards = document.querySelectorAll('.rp-card');
      const wrap = document.querySelector('.rp-cards-wrap');
      if (!wrap || cards.length < 5) return { ok: false, reason: 'not enough cards' };
      const wrapRect = wrap.getBoundingClientRect();
      let leftVisible = 0;
      let rightVisible = 0;
      cards.forEach(card => {
        const rect = card.getBoundingClientRect();
        if (rect.right > wrapRect.left && rect.left < wrapRect.right) {
          if (rect.left < wrapRect.left + 20) leftVisible++;
          if (rect.right > wrapRect.right - 20) rightVisible++;
        }
      });
      return { ok: leftVisible > 0 && rightVisible > 0, leftVisible, rightVisible, total: cards.length };
    });
    console.log('Initial cards check:', initialCardsCheck);

    await page.locator('.bonus-roulette-section').screenshot({ path: path.join(SCREENSHOTS, '01-initial.png') });
    console.log('  screenshot 01-initial.png');

    // Spin 1: paid or free
    let spinCount = 0;
    const spin = async (label) => {
      const btnFree = await page.$('button.roulette-btn-free:not(:disabled)');
      const btnPaid = await page.$('button.roulette-btn-paid:not(:disabled)');
      const btn = btnFree || btnPaid;
      if (!btn) {
        console.log(`  ${label}: no available spin button, skipping`);
        return false;
      }
      const isFree = !!btnFree;
      console.log(`  ${label}: clicking ${isFree ? 'free' : 'paid'} spin...`);
      await btn.click();
      return true;
    };

    if (await spin('Spin 1')) {
      spinCount++;
      await wait(500);
      await page.locator('.bonus-roulette-section').screenshot({ path: path.join(SCREENSHOTS, '02-spin1-mid.png') });
      console.log('  screenshot 02-spin1-mid.png');
      await wait(1500);
      await page.locator('.bonus-roulette-section').screenshot({ path: path.join(SCREENSHOTS, '03-spin1-late.png') });
      console.log('  screenshot 03-spin1-late.png');
      await wait(2000);
      const message1 = await page.$eval('.roulette-message', el => el.textContent).catch(() => null);
      console.log('  Spin 1 result message:', message1);
      await page.locator('.bonus-roulette-section').screenshot({ path: path.join(SCREENSHOTS, '04-spin1-result.png') });
      console.log('  screenshot 04-spin1-result.png');
    }

    await wait(1200);

    // Spin 2: verify "infinite" behavior
    if (await spin('Spin 2')) {
      spinCount++;
      await wait(600);
      await page.locator('.bonus-roulette-section').screenshot({ path: path.join(SCREENSHOTS, '05-spin2-start.png') });
      console.log('  screenshot 05-spin2-start.png');
      await wait(3000);
      const message2 = await page.$eval('.roulette-message', el => el.textContent).catch(() => null);
      console.log('  Spin 2 result message:', message2);
      await page.locator('.bonus-roulette-section').screenshot({ path: path.join(SCREENSHOTS, '06-spin2-result.png') });
      console.log('  screenshot 06-spin2-result.png');
    }

    // Mobile snapshot
    console.log('Mobile snapshot...');
    await page.setViewportSize({ width: 390, height: 844 });
    await wait(800);
    await page.locator('.bonus-roulette-section').screenshot({ path: path.join(SCREENSHOTS, '07-mobile.png') });
    console.log('  screenshot 07-mobile.png');

    console.log(`\nDone. Performed ${spinCount} spin(s).`);
    if (!initialCardsCheck.ok) {
      console.warn('WARNING: initial state does not show cards on both sides of the pointer.');
    }
  } catch (e) {
    console.error('ERROR:', e.message);
    await page.screenshot({ path: path.join(SCREENSHOTS, 'error.png'), fullPage: false });
    process.exit(1);
  } finally {
    const video = page.video();
    await page.close();
    if (video) {
      const videoPath = await video.path().catch(() => null);
      if (videoPath) console.log('Video saved:', videoPath);
    }
    await context.close();
    await browser.close();
  }
})();
