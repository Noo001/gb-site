const fs = require('fs');
const url = process.argv[2];
if (!url) {
  console.error('usage: node e2e-bonus-free.js <signed-url>');
  process.exit(1);
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
    await page.screenshot({ path: 'tests/prototypes/prod-bonuses-free-before.png' });

    const freeBtn = await page.$('.roulette-btn-free');
    if (freeBtn) {
      await freeBtn.evaluate(b => b.click());
      await page.waitForTimeout(7000);
      await page.screenshot({ path: 'tests/prototypes/prod-bonuses-free-after.png' });
    } else {
      msgs.push('NOFREE');
    }
  } catch (e) {
    console.error('test error:', e.message);
    msgs.push('TESTERROR: ' + e.message);
  } finally {
    await browser.close();
    fs.writeFileSync('tests/prototypes/prod-console-free.log', JSON.stringify(msgs, null, 2));
    console.log('done, messages:', msgs.length);
  }
})();
