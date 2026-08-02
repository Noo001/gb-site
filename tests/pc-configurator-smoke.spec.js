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
    viewport: { width: 1920, height: 1080 },
  });
  const page = await context.newPage();
  const results = [];

  const screenshot = async (name) => {
    const safe = translit(name) || 'unnamed';
    await page.screenshot({ path: `tests/screenshots/${safe}.png`, fullPage: false });
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
  const ok = (msg) => { console.log('  ✓', msg); results.push({ status: 'ok', msg }); };
  const fail = (msg) => { console.log('  ✗', msg); results.push({ status: 'fail', msg }); };

  try {
    console.log('Opening PC configurator...');
    await page.goto(`${BASE}/pc`, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForSelector('.pc-wizard', { timeout: 15000 });
    ok('Configurator page loaded');
    await screenshot('pc-landing');

    // 1. Open motherboard step and select ASUS PRIME B760M-K (2 RAM slots)
    const mbSvg = await page.$('g.pc-slot[data-slot="motherboard"]');
    if (!mbSvg) {
      fail('Motherboard SVG slot not found');
    } else {
      await safeClick(mbSvg, 'Motherboard SVG slot');
      await page.waitForTimeout(800);
      ok('Opened motherboard step via SVG');
    }

    const mbBtn = await page.$('button.pc-part:has(.pc-part-name:has-text("ASUS PRIME B760M-K"))');
    if (!mbBtn) {
      fail('Motherboard ASUS PRIME B760M-K not found');
    } else {
      await safeClick(mbBtn, 'ASUS PRIME B760M-K');
      await page.waitForTimeout(500);
      ok('Selected ASUS PRIME B760M-K (2 RAM slots)');
      await screenshot('pc-motherboard-selected');
    }

    // 2. Open RAM step
    const ramStep = await page.$('button.pc-step-head:has(.pc-step-title:has-text("Память"))');
    if (!ramStep) {
      fail('RAM step not found');
    } else {
      await safeClick(ramStep, 'RAM step');
      await page.waitForTimeout(800);
      ok('Opened RAM step');
      await screenshot('pc-ram-step');
    }

    // 3. Find Kingston Fury 16GB DDR5 and spam the + button
    const ramCard = await page.$('button.pc-part:has(.pc-part-name:has-text("Kingston Fury 16GB DDR5"))');
    if (!ramCard) {
      fail('RAM Kingston Fury 16GB DDR5 not found');
    } else {
      const plus = await page.$('button.pc-part:has(.pc-part-name:has-text("Kingston Fury 16GB DDR5")) button.pc-qty-btn:has-text("+")');
      if (!plus) {
        fail('RAM + button not found');
      } else {
        // Click + 10 times (should stop at 2 because of memory_slots, stock is 5)
        for (let i = 0; i < 10; i++) {
          await safeClick(plus, 'RAM +');
          await page.waitForTimeout(150);
        }
        const qtyValue = await page.$eval('button.pc-part:has(.pc-part-name:has-text("Kingston Fury 16GB DDR5")) .pc-qty-value', el => el.innerText).catch(() => null);
        const qty = qtyValue ? parseInt(qtyValue.trim(), 10) : NaN;
        if (!isNaN(qty) && qty <= 2) {
          ok(`RAM qty capped by motherboard slots: ${qty} (max 2)`);
        } else {
          fail(`RAM qty not capped by slots: ${qty} (expected <= 2)`);
        }
        await screenshot('pc-ram-capped');
      }
    }

    // 4. Verify stock cap on a multi slot (extra fans, stock = 5)
    const extraSvg = await page.$('g.pc-slot[data-slot="extra"]');
    if (extraSvg) {
      await safeClick(extraSvg, 'Extra SVG slot');
      await page.waitForTimeout(1000);
      const fanCard = await page.$('button.pc-part:has(.pc-part-name:has-text("Arctic P12"))');
      if (fanCard) {
        const plusFan = await page.$('button.pc-part:has(.pc-part-name:has-text("Arctic P12")) button.pc-qty-btn:has-text("+")');
        if (plusFan) {
          for (let i = 0; i < 10; i++) {
            await safeClick(plusFan, 'Fan +');
            await page.waitForTimeout(150);
          }
          const qtyValue = await page.$eval('button.pc-part:has(.pc-part-name:has-text("Arctic P12")) .pc-qty-value', el => el.innerText).catch(() => null);
          const qty = qtyValue ? parseInt(qtyValue.trim(), 10) : NaN;
          if (!isNaN(qty) && qty <= 5) {
            ok(`Extra fan qty capped by stock: ${qty} (max 5)`);
          } else {
            fail(`Extra fan qty not capped by stock: ${qty} (expected <= 5)`);
          }
          await screenshot('pc-extra-stock-capped');
        }
      }
    }

  } catch (e) {
    fail(`Global: ${e.message}`);
    await screenshot('pc-error');
  } finally {
    await browser.close();
  }

  console.log('\n=== Results ===');
  const passed = results.filter(r => r.status === 'ok').length;
  const failed = results.filter(r => r.status === 'fail').length;
  console.log(`${passed} passed, ${failed} failed`);
  for (const r of results.filter(r => r.status === 'fail')) console.log('FAIL:', r.msg);
  process.exit(failed > 0 ? 1 : 0);
})();
