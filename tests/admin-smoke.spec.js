const { chromium } = require('playwright');

const BASE = 'https://gbsale.ru';
const ADMIN_LOGIN = 'admin@gbsale.ru';
const ADMIN_PASSWORD = 'GB-admin-2026-x7Q';

const sections = [
  { path: '/admin/stores', name: 'Склады' },
  { path: '/admin/users', name: 'Пользователи' },
  { path: '/admin/products', name: 'Товары' },
  { path: '/admin/categories', name: 'Категории' },
  { path: '/admin/orders', name: 'Заказы' },
  { path: '/admin/offers', name: 'Предложения' },
  { path: '/admin/pages', name: 'Страницы' },
  { path: '/admin/redirects', name: 'Редиректы' },
  { path: '/admin/regions', name: 'Регионы' },
  { path: '/admin/seo-metadata', name: 'SEO' },
  { path: '/admin/bot-products', name: 'Bot Products' },
  { path: '/admin/bot-knowledges', name: 'Bot Knowledge' },
  { path: '/admin/bot-trade-in-prices', name: 'Bot Trade-in' },
  { path: '/admin/bot-action-logs', name: 'Bot Logs' },
];

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    httpCredentials: { username: ADMIN_LOGIN, password: ADMIN_PASSWORD },
    viewport: { width: 1920, height: 1080 },
  });
  const page = await context.newPage();
  const results = [];

  const screenshot = async (name) => {
    await page.screenshot({ path: `tests/screenshots/${name}.png`, fullPage: false });
  };

  const ok = (msg) => { console.log('  ✓', msg); results.push({ status: 'ok', msg }); };
  const fail = (msg) => { console.log('  ✗', msg); results.push({ status: 'fail', msg }); };

  try {
    console.log('Opening admin login...');
    await page.goto(`${BASE}/admin/login`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('input[type="email"]', { timeout: 10000 });
    await page.fill('input[type="email"]', ADMIN_LOGIN);
    await page.fill('input[type="password"]', ADMIN_PASSWORD);
    await page.click('button[type="submit"]');
    await page.waitForURL(`${BASE}/admin`, { timeout: 20000 });
    ok('Logged in to admin');
    await screenshot('admin-dashboard');

    for (const sec of sections) {
      console.log(`\nSection: ${sec.name} (${sec.path})`);
      try {
        await page.goto(`${BASE}${sec.path}`, { waitUntil: 'networkidle', timeout: 30000 });
        await page.waitForLoadState('networkidle');

        const error = await page.$eval('body', el => {
          const text = el.innerText;
          return text.includes('An Error Occurred') || text.includes('Whoops') || text.includes('Fatal error');
        });
        if (error) {
          fail(`${sec.name}: error page rendered`);
        } else {
          ok(`${sec.name}: page loaded`);
        }

        const hasTable = await page.$('table') !== null;
        if (hasTable) {
          ok(`${sec.name}: table present`);
          const tableWidth = await page.$eval('table', t => t.getBoundingClientRect().width);
          const viewportWidth = await page.evaluate(() => window.innerWidth);
          if (tableWidth >= viewportWidth * 0.85) {
            ok(`${sec.name}: table is wide (${Math.round(tableWidth)}px / ${viewportWidth}px)`);
          } else {
            fail(`${sec.name}: table is narrow (${Math.round(tableWidth)}px / ${viewportWidth}px)`);
          }
        } else {
          fail(`${sec.name}: table not found`);
        }

        const pagination = await page.$('select[name="tableRecordsPerPage"]');
        if (pagination) {
          ok(`${sec.name}: pagination dropdown present`);
          await page.click('select[name="tableRecordsPerPage"]');
          await page.selectOption('select[name="tableRecordsPerPage"]', '200');
          await page.waitForTimeout(1500);
          const selected = await page.$eval('select[name="tableRecordsPerPage"]', el => el.value);
          if (selected === '200') {
            ok(`${sec.name}: per-page set to 200`);
          } else {
            fail(`${sec.name}: per-page is ${selected} instead of 200`);
          }
        } else {
          fail(`${sec.name}: pagination dropdown not found`);
        }

        await screenshot(`admin-${sec.name.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`);
      } catch (e) {
        fail(`${sec.name}: ${e.message}`);
        await screenshot(`admin-error-${sec.name.toLowerCase().replace(/[^a-z0-9]+/g, '-')}`);
      }
    }

    // Check pagination persistence: reload stores page and verify per-page stays 200
    console.log('\nChecking pagination persistence...');
    try {
      await page.goto(`${BASE}/admin/stores`, { waitUntil: 'networkidle', timeout: 30000 });
      const selected = await page.$eval('select[name="tableRecordsPerPage"]', el => el.value);
      if (selected === '200') {
        ok('Stores: per-page persisted at 200 after reload');
      } else {
        fail(`Stores: per-page not persisted (${selected})`);
      }
    } catch (e) {
      fail(`Pagination persistence: ${e.message}`);
    }

  } catch (e) {
    fail(`Global error: ${e.message}`);
  } finally {
    await browser.close();
  }

  console.log('\n=== Results ===');
  const passed = results.filter(r => r.status === 'ok').length;
  const failed = results.filter(r => r.status === 'fail').length;
  console.log(`${passed} passed, ${failed} failed`);
  for (const r of results.filter(r => r.status === 'fail')) {
    console.log('FAIL:', r.msg);
  }
  process.exit(failed > 0 ? 1 : 0);
})();
