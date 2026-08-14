const { chromium } = require('playwright');
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
    console.log('Opening homepage...');
    await page.goto(`${BASE}/`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('header.site-header', { timeout: 15000 });
    console.log('  ✓ Homepage loaded');

    console.log('Clicking "Войти" in header...');
    const loginBtn = await page.$('button.action-link:has-text("Войти")');
    if (!loginBtn) throw new Error('Header login button not found');
    await loginBtn.click();
    await page.waitForTimeout(500);

    await page.waitForSelector('.auth-modal-overlay', { state: 'visible', timeout: 5000 });
    console.log('  ✓ Auth modal is visible');

    await page.screenshot({ path: path.join(SCREENSHOTS, 'auth-modal-header.png'), fullPage: false });
    console.log('  ✓ Screenshot saved');
  } catch (e) {
    console.log('  ✗', e.message);
    failed = true;
    await page.screenshot({ path: path.join(SCREENSHOTS, 'auth-modal-header-error.png'), fullPage: false });
  } finally {
    await browser.close();
  }

  process.exit(failed ? 1 : 0);
})();
