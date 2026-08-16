const { chromium } = require('playwright');
const { execSync } = require('child_process');
const path = require('path');

(async () => {
    const signedUrl = execSync('python tests/generate-e2e-url.py', { cwd: path.resolve('tests/..'), encoding: 'utf8', timeout: 30000 }).trim();
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({
        userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
        viewport: { width: 1280, height: 900 }
    });
    const page = await context.newPage();
    page.on('console', msg => {
        if (msg.type() === 'error') console.log('PAGE ERROR:', msg.text());
    });
    page.on('pageerror', err => console.log('JS ERROR:', err.message));

    await page.goto(signedUrl, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForURL('https://gbsale.ru/account/bonuses', { timeout: 20000 });
    await page.evaluate(() => window.scrollTo(0, 650));
    await page.waitForTimeout(500);

    // Initial states
    await page.screenshot({ path: 'tests/screenshots/check-pole-initial.png', fullPage: true });

    // Pole spin
    await page.click('button.roulette-btn-paid');
    await page.waitForTimeout(17500);
    await page.screenshot({ path: 'tests/screenshots/check-pole-spin.png', fullPage: true });

    // Market initial
    const tabs = await page.locator('button.wheel-tab').all();
    const tabTexts = await Promise.all(tabs.map(t => t.textContent()));
    let idx = tabTexts.findIndex(t => t.includes('Маркетплейс'));
    await tabs[idx].click();
    await page.waitForTimeout(800);
    await page.screenshot({ path: 'tests/screenshots/check-market-initial.png', fullPage: true });

    // Market spin
    await page.click('button.roulette-btn-paid');
    await page.waitForTimeout(8500);
    await page.screenshot({ path: 'tests/screenshots/check-market-spin.png', fullPage: true });

    await browser.close();
})();
