const fs = require('fs');
const url = process.argv[2];
if (!url) {
  console.error('usage: node e2e-bonus-mobile.js <signed-url>');
  process.exit(1);
}

async function clickTab(page, label) {
  await page.evaluate((lbl) => {
    const tabs = Array.from(document.querySelectorAll('.wheel-tab'));
    const tab = tabs.find(t => t.textContent.trim().includes(lbl));
    if (tab) tab.click();
  }, label);
  await page.waitForTimeout(800);
}

(async () => {
  const { chromium } = require('playwright');
  const browser = await chromium.launch({ headless: false });
  const context = await browser.newContext({
    userAgent: 'Mozilla/5.0 (iPhone; CPU iPhone OS 17_0 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.0 Mobile/15E148 Safari/604.1',
    viewport: { width: 375, height: 667 }
  });
  const page = await context.newPage();
  const msgs = [];
  page.on('console', m => msgs.push(m.type() + ': ' + m.text()));
  page.on('pageerror', e => msgs.push('PAGEERROR: ' + e.message));
  page.on('requestfailed', r => msgs.push('REQFAIL: ' + r.url() + ' ' + r.failure().errorText));

  try {
    await page.goto(url, { waitUntil: 'networkidle' });
    await page.waitForTimeout(3000);
    await page.screenshot({ path: 'tests/prototypes/prod-bonuses-mobile-pole.png', fullPage: true });

    await clickTab(page, 'Маркет');
    await page.screenshot({ path: 'tests/prototypes/prod-bonuses-mobile-market.png', fullPage: true });

    await clickTab(page, 'Neon');
    await page.screenshot({ path: 'tests/prototypes/prod-bonuses-mobile-neon.png', fullPage: true });
  } catch (e) {
    console.error('test error:', e.message);
    msgs.push('TESTERROR: ' + e.message);
  } finally {
    await browser.close();
    fs.writeFileSync('tests/prototypes/prod-console-mobile.log', JSON.stringify(msgs, null, 2));
    console.log('done, messages:', msgs.length);
  }
})();
