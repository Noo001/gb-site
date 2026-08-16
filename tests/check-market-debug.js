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

    await page.goto(signedUrl, { waitUntil: 'networkidle', timeout: 30000 });
    await page.waitForURL('https://gbsale.ru/account/bonuses', { timeout: 20000 });
    await page.evaluate(() => window.scrollTo(0, 650));
    await page.waitForTimeout(500);

    const tabs = await page.locator('button.wheel-tab').all();
    const tabTexts = await Promise.all(tabs.map(t => t.textContent()));
    let idx = tabTexts.findIndex(t => t.includes('Маркетплейс'));
    await tabs[idx].click();
    await page.waitForTimeout(800);

    const before = await page.evaluate(() => {
        const track = document.querySelector('.rp-cards-track');
        const wrap = document.querySelector('.rp-cards-wrap');
        return {
            trackTransform: track?.style.transform,
            trackComputed: getComputedStyle(track).transform,
            wrapWidth: wrap?.clientWidth,
            cardsCount: track?.querySelectorAll('.rp-card').length,
            firstCardLeft: track?.querySelector('.rp-card')?.getBoundingClientRect().left
        };
    });
    console.log('Before spin:', before);

    await page.click('button.roulette-btn-paid');
    await page.waitForTimeout(8500);

    const after = await page.evaluate(() => {
        const track = document.querySelector('.rp-cards-track');
        const wrap = document.querySelector('.rp-cards-wrap');
        const cards = Array.from(track.querySelectorAll('.rp-card'));
        const wrapRect = wrap.getBoundingClientRect();
        const center = wrapRect.left + wrap.clientWidth / 2;
        let closest = null;
        let minDist = Infinity;
        cards.forEach(card => {
            const rect = card.getBoundingClientRect();
            const dist = Math.abs(rect.left + rect.width / 2 - center);
            if (dist < minDist) {
                minDist = dist;
                closest = { label: card.querySelector('.rp-card-label')?.textContent, dist, left: rect.left, width: rect.width };
            }
        });
        return {
            trackTransform: track?.style.transform,
            trackComputed: getComputedStyle(track).transform,
            wrapWidth: wrap?.clientWidth,
            cardsCount: cards.length,
            closest,
            visibleCards: cards.filter(c => {
                const r = c.getBoundingClientRect();
                return r.right > wrapRect.left && r.left < wrapRect.right;
            }).map(c => c.querySelector('.rp-card-label')?.textContent)
        };
    });
    console.log('After spin:', after);

    await page.screenshot({ path: 'tests/screenshots/check-market-debug.png', fullPage: true });
    await browser.close();
})();
