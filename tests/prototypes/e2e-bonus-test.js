const fs = require('fs');
const url = process.argv[2];
if (!url) {
  console.error('usage: node e2e-bonus-test.js <signed-url>');
  process.exit(1);
}
const env = {};
for (const line of fs.readFileSync('.env.secrets', 'utf8').split('\n')) {
  const t = line.trim();
  if (!t || t.startsWith('#') || !t.includes('=')) continue;
  const [k, ...rest] = t.split('=');
  env[k] = rest.join('=').trim().replace(/^['"]|['"]$/g, '');
}

async function clickTab(page, label) {
  await page.evaluate((lbl) => {
    const tabs = Array.from(document.querySelectorAll('.wheel-tab'));
    const tab = tabs.find(t => t.textContent.trim().includes(lbl));
    if (tab) tab.click();
  }, label);
  await page.waitForTimeout(800);
}

async function scrollToWheel(page) {
  await page.evaluate(() => {
    const el = document.querySelector('.bonus-roulette-section');
    if (el) el.scrollIntoView({ block: 'start' });
  });
  await page.waitForTimeout(400);
}

(async () => {
  const { chromium } = require('playwright');
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/126.0.0.0 Safari/537.36'
  });
  const page = await context.newPage({ viewport: { width: 1280, height: 900 } });
  const msgs = [];
  page.on('console', m => msgs.push(m.type() + ': ' + m.text()));
  page.on('pageerror', e => msgs.push('PAGEERROR: ' + e.message));
  page.on('requestfailed', r => msgs.push('REQFAIL: ' + r.url() + ' ' + r.failure().errorText));

  try {
    await page.goto(url, { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);

    await scrollToWheel(page);
    await page.screenshot({ path: 'tests/prototypes/prod-bonuses-pole.png' });

    // spin Pole (paid)
    const paidBtn = await page.$('.roulette-btn-paid');
    if (paidBtn) {
      await paidBtn.evaluate(b => b.click());
      await page.waitForTimeout(7000);
      await page.screenshot({ path: 'tests/prototypes/prod-bonuses-pole-after-spin.png' });
    }

    await clickTab(page, 'Маркет');
    await page.screenshot({ path: 'tests/prototypes/prod-bonuses-market.png' });
    const marketPaid = await page.$('.roulette-btn-paid');
    if (marketPaid) {
      await marketPaid.evaluate(b => b.click());
      await page.waitForTimeout(4500);
      await page.screenshot({ path: 'tests/prototypes/prod-bonuses-market-after-spin.png' });
    }

    await clickTab(page, 'Neon');
    await page.screenshot({ path: 'tests/prototypes/prod-bonuses-neon.png' });
    const neonPaid = await page.$('.roulette-btn-paid');
    if (neonPaid) {
      await neonPaid.evaluate(b => b.click());
      await page.waitForTimeout(6000);
      await page.screenshot({ path: 'tests/prototypes/prod-bonuses-neon-after-spin.png' });
    }

    await scrollToWheel(page);
    await page.screenshot({ path: 'tests/prototypes/prod-bonuses-final.png' });
  } catch (e) {
    console.error('test error:', e.message);
    msgs.push('TESTERROR: ' + e.message);
  } finally {
    await browser.close();
    fs.writeFileSync('tests/prototypes/prod-console.log', JSON.stringify(msgs, null, 2));
    console.log('done, messages:', msgs.length);
  }
})();
