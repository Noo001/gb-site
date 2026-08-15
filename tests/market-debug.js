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
    page.on('console', msg => console.log('PAGE LOG:', msg.type(), msg.text()));
    page.on('pageerror', err => console.log('PAGE ERROR:', err.message));
    await page.goto(signedUrl, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForURL('https://gbsale.ru/account/bonuses', { timeout: 20000 });
    await page.evaluate(() => window.scrollTo(0, 650));
    await page.waitForTimeout(500);
    const tabs = await page.locator('button.wheel-tab').all();
    await tabs[1].click();
    await page.waitForTimeout(800);
    await page.click('button.roulette-btn-paid');
    await page.waitForTimeout(8000);
    const active = await page.evaluate(() => {
        const el = document.querySelector('.rp-card.active');
        return el ? { label: el.querySelector('.rp-card-label')?.textContent, sub: el.querySelector('.rp-card-sub')?.textContent } : null;
    });
    const msg = await page.evaluate(() => document.querySelector('.roulette-message')?.textContent);
    const spinning = await page.evaluate(() => {
        const el = document.querySelector('.bonus-page');
        if (!el || !window.Alpine) return null;
        const api = Alpine.$data(el);
        return api ? api.spinning : null;
    });
    console.log('Active card:', JSON.stringify(active), 'Message:', msg, 'Spinning:', spinning);
    await page.screenshot({ path: 'tests/screenshots/bonus-market-debug.png', fullPage: true });
    await browser.close();
})();
