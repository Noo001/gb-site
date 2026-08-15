const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');

const BASE = 'https://gbsale.ru';
const SCREENSHOTS = path.resolve(__dirname, 'screenshots', 'bonus-wheels');

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

const screenshot = async (page, name, fullPage = false) => {
  try {
    const safe = translit(name) || 'unnamed';
    await page.screenshot({ path: path.join(SCREENSHOTS, `${safe}.png`), fullPage });
    console.log('  📸', name);
  } catch (e) {
    console.log('  ⚠ screenshot failed:', e.message);
  }
};

const ok = (msg) => console.log('  ✓', msg);
const fail = (msg) => { console.log('  ✗', msg); return msg; };

const getCaptchaCode = async (page) => {
  const repoRoot = path.resolve(__dirname, '..');
  const signedUrl = execSync('python tests/generate-e2e-captcha-url.py', {
    cwd: repoRoot,
    encoding: 'utf8',
    timeout: 30000,
  }).trim();
  if (!signedUrl.startsWith('http')) throw new Error('unexpected captcha url: ' + signedUrl);
  await page.request.get(`${BASE}/captcha`);
  const res = await page.request.get(signedUrl);
  const response = await res.json();
  if (!response.code) throw new Error('captcha code not returned: ' + JSON.stringify(response));
  return response.code;
};

const wait = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ userAgent: USER_AGENT, viewport: { width: 1920, height: 1080 } });
  const page = await context.newPage();
  const errors = [];

  try {
    console.log('\nRegistering test user...');
    await page.goto(`${BASE}/register`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('form[action="/register"]', { timeout: 15000 });
    ok('Registration form loaded');

    const timestamp = Date.now();
    const email = `wheel-${timestamp}@gbsale.ru`;
    const phone = `7900${timestamp.toString().slice(-7)}`;
    const captcha = await getCaptchaCode(page);

    await page.fill('input[name="name"]', 'Wheel Test User');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="phone"]', phone);
    await page.fill('input[name="password"]', 'e2e-test-password');
    await page.fill('input[name="password_confirmation"]', 'e2e-test-password');
    await page.fill('input[name="captcha"]', captcha);
    await page.check('input[name="privacy"]');

    await Promise.all([
      page.waitForURL(`${BASE}/`, { timeout: 20000 }),
      page.click('form[action="/register"] button[type="submit"]'),
    ]);
    ok('Registered');

    console.log('\nOpening /account/bonuses...');
    await page.goto(`${BASE}/account/bonuses`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('.bonus-page', { timeout: 15000 });
    ok('Bonus page loaded');

    // Accept terms if presented
    const acceptTermsBtn = await page.locator('button:has-text("Принять условия")').first();
    if (await acceptTermsBtn.isVisible().catch(() => false)) {
      console.log('  Accepting terms...');
      await Promise.all([
        page.waitForURL(`${BASE}/account/bonuses*`, { waitUntil: 'networkidle', timeout: 20000 }).catch(() => {}),
        acceptTermsBtn.click(),
      ]);
      await page.waitForTimeout(1500);
      ok('Accepted bonus terms');
    } else {
      console.log('  No terms accept button found');
    }

    await screenshot(page, 'bonus-page-initial', true);

    const variants = [
      { key: 'pole', name: 'Поле чудес 3D', selector: '.wheel-tab:has-text("Поле чудес 3D")', wheel: '.pc-wheel-3d' },
      { key: 'market', name: 'Маркетплейс', selector: '.wheel-tab:has-text("Маркетплейс")', wheel: '.market-wheel' },
      { key: 'neon', name: 'Neon Fusion', selector: '.wheel-tab:has-text("Neon Fusion")', wheel: '.neon-wheel' },
    ];

    for (const v of variants) {
      console.log(`\nTesting ${v.name}...`);
      await page.click(v.selector);
      await wait(800);
      await page.waitForSelector(v.wheel, { timeout: 10000 });
      ok(`${v.name} wheel visible`);
      await screenshot(page, `bonus-${v.key}-static`, true);

      const balanceBefore = await page.$eval('.bonus-balance-value span', el => el.textContent.replace(/\s/g, ''));
      console.log(`  Balance before: ${balanceBefore}`);

      await page.click('.roulette-btn-paid');
      ok('Spin started');
      await wait(1500);
      await screenshot(page, `bonus-${v.key}-spinning`, true);
      await wait(6500);
      await screenshot(page, `bonus-${v.key}-result`, true);

      const message = await page.$eval('.roulette-message', el => el.textContent).catch(() => '');
      const balanceAfter = await page.$eval('.bonus-balance-value span', el => el.textContent.replace(/\s/g, ''));
      console.log(`  Message: ${message}`);
      console.log(`  Balance after: ${balanceAfter}`);
      if (!message) errors.push(`No message for ${v.name}`);
      if (parseInt(balanceAfter) === parseInt(balanceBefore)) {
        errors.push(`Balance unchanged for ${v.name} (before ${balanceBefore}, after ${balanceAfter})`);
      }
    }

    console.log('\nFinal screenshot...');
    await page.click('.wheel-tab:has-text("Поле чудес 3D")');
    await wait(500);
    await screenshot(page, 'bonus-page-final', true);

  } catch (e) {
    errors.push(e.message);
    console.error('ERROR:', e.message);
    await screenshot(page, 'bonus-error', true);
  } finally {
    await browser.close();
  }

  console.log('\n=== RESULTS ===');
  if (errors.length === 0) {
    console.log('All wheel variants passed');
  } else {
    console.log('Errors:');
    errors.forEach(e => console.log(' -', e));
    process.exit(1);
  }
})();
