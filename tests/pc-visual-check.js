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
  });
  const page = await context.newPage();

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

  // Desktop landing
  await page.setViewportSize({ width: 1440, height: 900 });
  await page.goto(`${BASE}/pc`, { waitUntil: 'networkidle', timeout: 30000 });
  await page.waitForSelector('.pc-wizard', { timeout: 15000 });
  await page.waitForTimeout(1200); // let rotation finish
  await screenshot('pc-desktop-v2');

  // Click GPU step to see rotation
  const gpuStep = await page.$('button.pc-step-head:has(.pc-step-title:has-text("Видеокарта"))');
  if (gpuStep) {
    await safeClick(gpuStep, 'GPU step');
    await page.waitForTimeout(1200);
    await screenshot('pc-desktop-rotated-gpu-v2');
  } else {
    console.log('GPU step not found');
  }

  // Mobile landing
  await page.setViewportSize({ width: 390, height: 844 });
  await page.reload({ waitUntil: 'networkidle' });
  await page.waitForSelector('.pc-wizard', { timeout: 15000 });
  await page.waitForTimeout(800);
  await screenshot('pc-mobile-v2');

  await browser.close();
})();
