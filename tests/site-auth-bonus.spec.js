const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'https://gbsale.ru';
const SCREENSHOTS = path.resolve(__dirname, 'screenshots');

const USER_AGENT = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36';

const translit = (str) => {
  return str.toLowerCase()
    .replace(/ё/g, 'yo').replace(/[ъь]/g, '')
    .replace(/[а-я]/g, c => ({
      а:'a',б:'b',в:'v',г:'g',д:'d',е:'e',ж:'zh',з:'z',и:'i',й:'y',к:'k',
      л:'l',м:'m',н:'n',о:'o',п:'p',р:'r',с:'s',т:'t',у:'u',ф:'f',х:'h',
      ц:'ts',ч:'ch',ш:'sh',щ:'sch',ы:'y',э:'e',ю:'yu',я:'ya'
    }[c]) || c)
    .replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
};

const screenshot = async (page, name) => {
  const safe = translit(name) || 'unnamed';
  await page.screenshot({ path: path.join(SCREENSHOTS, `${safe}.png`), fullPage: false });
};

const ok = (msg) => console.log('  ✓', msg);
const fail = (msg) => { console.log('  ✗', msg); return msg; };

(async () => {
  const results = [];

  let signedUrl = '';
  try {
    const repoRoot = path.resolve(__dirname, '..');
    signedUrl = execSync('python tests/generate-e2e-url.py', {
      cwd: repoRoot,
      encoding: 'utf8',
      timeout: 30000,
    }).trim();
    if (!signedUrl.startsWith('http')) throw new Error('unexpected url: ' + signedUrl);
    ok('Generated signed e2e login URL');
  } catch (e) {
    console.error('Failed to generate signed URL:', e.message);
    process.exit(1);
  }

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    userAgent: USER_AGENT,
    viewport: { width: 1920, height: 1080 },
  });
  const page = await context.newPage();

  const safeClick = async (selector, name) => {
    const el = await page.$(selector);
    if (!el) throw new Error(`${name} not found (${selector})`);
    await el.evaluate(node => node.scrollIntoView({ block: 'center', inline: 'center' }));
    await page.waitForTimeout(150);
    await el.click();
    await page.waitForTimeout(300);
  };

  try {
    console.log('\nOpening homepage...');
    await page.goto(`${BASE}/`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('header.site-header', { timeout: 15000 });
    ok('Homepage loaded');
    await screenshot(page, 'home');

    console.log('\nOpening login modal...');
    const loginBtn = await page.$('button.action-link:has-text("Войти")');
    if (!loginBtn) throw new Error('Login button not found');
    await loginBtn.evaluate(() => window.dispatchEvent(new CustomEvent('open-auth-modal')));
    await page.waitForTimeout(800);
    await page.waitForSelector('.auth-modal', { state: 'visible', timeout: 5000 });
    ok('Login modal opened');
    await screenshot(page, 'modal-login');

    console.log('\nLogging in via signed URL...');
    await page.goto(signedUrl, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForURL(`${BASE}/account/bonuses`, { timeout: 20000 });
    ok('Authenticated on /account/bonuses');
    await screenshot(page, 'account-bonuses');

    // Accept bonus terms if the button is shown.
    const acceptTermsBtn = await page.$('button[type="submit"].btn-primary:has-text("Принять условия")');
    if (acceptTermsBtn) {
      await acceptTermsBtn.click();
      await page.waitForTimeout(1000);
      ok('Accepted bonus terms');
      await screenshot(page, 'account-bonuses-accepted');
    } else {
      ok('Bonus terms already accepted');
    }

    console.log('\nCollecting daily bonus...');
    await safeClick('button:has-text("Собрать бонусы")', 'Daily collect button');
    await page.waitForTimeout(1500);
    ok('Daily bonus collected');
    await screenshot(page, 'account-bonuses-daily');

    console.log('\nSpinning the wheel (free spin)...');
    await page.waitForFunction(() => {
      const btn = document.querySelector('button.roulette-btn-free');
      return btn && !btn.disabled;
    }, { timeout: 10000 });
    await safeClick('button:has-text("Бесплатная прокрутка")', 'Free spin button');
    await page.waitForSelector('.roulette-message', { timeout: 10000 });
    await page.waitForTimeout(500);
    ok('Wheel spin completed');
    await screenshot(page, 'account-bonuses-spin');

    console.log('\nNavigating to account dashboard...');
    await page.goto(`${BASE}/account`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('.account-layout', { timeout: 10000 });
    ok('Account dashboard loaded');
    await screenshot(page, 'account-dashboard');

    console.log('\nNavigating to account profile...');
    await page.goto(`${BASE}/account/profile`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('.account-layout', { timeout: 10000 });
    ok('Account profile loaded');
    await screenshot(page, 'account-profile');

    console.log('\nNavigating to account orders...');
    await page.goto(`${BASE}/account/orders`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('.account-layout', { timeout: 10000 });
    ok('Account orders loaded');
    await screenshot(page, 'account-orders');

    console.log('\nChecking auth state in header...');
    const cabinetLink = await page.$('a[href="/account"]:has-text("Кабинет")');
    if (cabinetLink) {
      ok('Header shows authenticated user link');
    } else {
      results.push(fail('Header does not show authenticated user link'));
    }

  } catch (e) {
    results.push(fail(`Global: ${e.message}`));
    await screenshot(page, 'error-global');
  } finally {
    await browser.close();
  }

  console.log('\n=== Results ===');
  const passed = results.filter(r => !r).length; // results stores failure strings
  const failed = results.filter(r => r).length;
  const totalChecks = results.length;
  console.log(`${totalChecks - failed} passed, ${failed} failed`);
  for (const r of results.filter(r => r)) console.log('FAIL:', r);
  process.exit(failed > 0 ? 1 : 0);
})();
