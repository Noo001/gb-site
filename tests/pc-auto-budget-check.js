const { chromium } = require('playwright');

const BASE = 'https://gbsale.ru';
const BASIC_LOGIN = 'admin@gbsale.ru';
const BASIC_PASSWORD = 'GB-admin-2026-x7Q';

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

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    httpCredentials: { username: BASIC_LOGIN, password: BASIC_PASSWORD },
    extraHTTPHeaders: { 'Cache-Control': 'no-cache' },
  });
  const page = await context.newPage();
  await page.setViewportSize({ width: 1440, height: 900 });

  const screenshot = async (name) => {
    const safe = translit(name) || 'unnamed';
    await page.screenshot({ path: `tests/screenshots/${safe}.png`, fullPage: false });
    console.log('screenshot:', safe);
  };

  const safeClick = async (handle, name) => {
    if (!handle) throw new Error(`${name} not found`);
    await handle.evaluate(el => el.scrollIntoView({ block: 'center', inline: 'center' }));
    await page.waitForTimeout(200);
    await handle.evaluate(el => {
      const rect = el.getBoundingClientRect();
      const x = rect.left + rect.width / 2;
      const y = rect.top + rect.height / 2;
      el.dispatchEvent(new MouseEvent('click', { bubbles: true, cancelable: true, clientX: x, clientY: y }));
    });
  };

  await page.goto(`${BASE}/pc`, { waitUntil: 'networkidle', timeout: 30000 });
  await page.waitForSelector('.pc-mode-tabs', { timeout: 15000 });
  await page.waitForTimeout(500);

  const autoTab = await page.$('button.pc-mode-tab:has-text("Автоподбор по бюджету")');
  if (autoTab) await safeClick(autoTab, 'Auto tab');
  await page.waitForTimeout(300);

  await page.fill('input[type="number"]', '200000');
  const submitBtn = await page.$('button.pc-submit-btn:has-text("Подобрать конфигурацию")');
  if (submitBtn) await safeClick(submitBtn, 'Auto submit');
  await page.waitForTimeout(2000);
  await page.evaluate(() => window.scrollTo(0, document.body.scrollHeight));
  await page.waitForTimeout(300);
  await screenshot('pc-auto-result-200k-buttons');

  // Also capture the result area scrolled to top to see the list and total
  await page.evaluate(() => window.scrollTo(0, 0));
  await page.waitForTimeout(300);
  await screenshot('pc-auto-result-200k');

  await browser.close();
})();
