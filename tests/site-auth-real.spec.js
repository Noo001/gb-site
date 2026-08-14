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
  try {
    const safe = translit(name) || 'unnamed';
    await page.screenshot({ path: path.join(SCREENSHOTS, `${safe}.png`), fullPage: false });
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

  // Set the session cookie by requesting the captcha image in the same context,
  // without leaving the registration/login page.
  await page.request.get(`${BASE}/captcha`);

  const res = await page.request.get(signedUrl);
  const response = await res.json();

  if (!response.code) throw new Error('captcha code not returned: ' + JSON.stringify(response));
  return response.code;
};

(async () => {
  const results = [];

  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    userAgent: USER_AGENT,
    viewport: { width: 1920, height: 1080 },
  });
  const page = await context.newPage();

  try {
    console.log('\nOpening registration page...');
    await page.goto(`${BASE}/register`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('form[action="/register"]', { timeout: 15000 });
    ok('Registration form loaded');
    await screenshot(page, 'register-form');

    const captchaCode = await getCaptchaCode(page);
    ok('Got captcha code via signed e2e route');

    const timestamp = Date.now();
    const email = `e2e-${timestamp}@gbsale.ru`;
    const phone = `7900${timestamp.toString().slice(-7)}`;

    await page.fill('input[name="name"]', 'E2E Test User');
    await page.fill('input[name="email"]', email);
    await page.fill('input[name="phone"]', phone);
    await page.fill('input[name="password"]', 'e2e-test-password');
    await page.fill('input[name="password_confirmation"]', 'e2e-test-password');
    await page.fill('input[name="captcha"]', captchaCode);
    await page.check('input[name="privacy"]');

    console.log('\nSubmitting registration...');
    await Promise.all([
      page.waitForURL(`${BASE}/`, { timeout: 20000 }),
      page.click('form[action="/register"] button[type="submit"]'),
    ]);
    ok('Registered and redirected to home');
    await screenshot(page, 'register-success');

    console.log('\nChecking authenticated state...');
    await page.goto(`${BASE}/account`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('.account-layout', { timeout: 10000 });
    ok('Account dashboard accessible after registration');
    await screenshot(page, 'account-after-register');

    console.log('\nLogging out...');
    await page.goto(`${BASE}/logout`, { waitUntil: 'networkidle', timeout: 30000 });
    ok('Logged out');

    console.log('\nOpening login page...');
    await page.goto(`${BASE}/login`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('form[action="/login"]', { timeout: 15000 });
    ok('Login form loaded');
    await screenshot(page, 'login-form');

    const loginCaptcha = await getCaptchaCode(page);

    await page.fill('input[name="login"]', email);
    await page.fill('input[name="password"]', 'e2e-test-password');
    await page.fill('input[name="captcha"]', loginCaptcha);

    console.log('\nSubmitting login...');
    await Promise.all([
      page.waitForURL(`${BASE}/**`, { timeout: 20000 }),
      page.click('form[action="/login"] button[type="submit"]'),
    ]);
    ok('Logged in');
    await screenshot(page, 'login-success');

    console.log('\nVerifying account page...');
    await page.goto(`${BASE}/account`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('.account-layout', { timeout: 10000 });
    const header = await page.$eval('.account-layout h1', el => el.textContent.trim()).catch(() => null);
    if (header) {
      ok(`Account header: "${header}"`);
    } else {
      results.push(fail('Account header not found'));
    }
    await screenshot(page, 'account-after-login');

  } catch (e) {
    results.push(fail(`Global: ${e.message}`));
    await screenshot(page, 'error-global');
  } finally {
    await browser.close();
  }

  console.log('\n=== Results ===');
  const failed = results.filter(r => r).length;
  console.log(`${results.length - failed} passed, ${failed} failed`);
  for (const r of results.filter(r => r)) console.log('FAIL:', r);
  process.exit(failed > 0 ? 1 : 0);
})();
