const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const URL = 'https://fr.gbsale.ru';
const SCREENSHOTS = path.join(__dirname, 'screenshots', 'franchise-' + Date.now());

if (!fs.existsSync(SCREENSHOTS)) fs.mkdirSync(SCREENSHOTS, { recursive: true });

const wait = (ms) => new Promise(r => setTimeout(r, ms));

(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({
    viewport: { width: 1440, height: 900 },
    userAgent: 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
    ignoreHTTPSErrors: true,
  });
  const page = await context.newPage();

  try {
    console.log('Loading', URL);
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await wait(2000);

    // Hero screenshot
    await page.screenshot({ path: path.join(SCREENSHOTS, '01-hero.png'), fullPage: false });
    console.log('Hero screenshot saved');

    // Scroll to brand story
    await page.evaluate(() => document.getElementById('about').scrollIntoView({ behavior: 'instant' }));
    await wait(800);
    await page.screenshot({ path: path.join(SCREENSHOTS, '02-about.png'), fullPage: false });

    // Scroll to formats
    await page.evaluate(() => document.getElementById('formats').scrollIntoView({ behavior: 'instant' }));
    await wait(800);
    await page.screenshot({ path: path.join(SCREENSHOTS, '03-formats.png'), fullPage: false });

    // Scroll to calculator
    await page.evaluate(() => document.getElementById('calculator').scrollIntoView({ behavior: 'instant' }));
    await wait(800);
    await page.screenshot({ path: path.join(SCREENSHOTS, '04-calculator.png'), fullPage: false });

    // Test calculator interaction
    const calcValue = await page.$eval('.fr-result-row.total span:last-child', el => el.textContent);
    console.log('Calculator initial net profit:', calcValue);

    // Move orders slider
    const ordersInput = await page.$('#orders');
    await ordersInput.evaluate(el => { el.value = 80; el.dispatchEvent(new Event('input', { bubbles: true })); });
    await wait(500);
    const calcValueAfter = await page.$eval('.fr-result-row.total span:last-child', el => el.textContent);
    console.log('Calculator after 80 orders:', calcValueAfter);

    // Scroll to forms
    await page.evaluate(() => document.getElementById('forms').scrollIntoView({ behavior: 'instant' }));
    await wait(800);
    await page.screenshot({ path: path.join(SCREENSHOTS, '05-forms.png'), fullPage: false });

    // Full page — scroll through to trigger reveal animations first
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await wait(1500);
    await page.evaluate(async () => {
      const step = window.innerHeight * 0.6;
      for (let y = 0; y <= document.body.scrollHeight; y += step) {
        window.scrollTo(0, y);
        await new Promise(r => setTimeout(r, 250));
      }
      window.scrollTo(0, 0);
    });
    await wait(500);
    await page.screenshot({ path: path.join(SCREENSHOTS, '06-full.png'), fullPage: true });
    console.log('Full page screenshot saved');

    // Test form (without actual submit to avoid spam)
    const firstForm = await page.$('#forms .fr-form-card');
    const nameInput = await firstForm.$('input[type="text"]');
    await nameInput.fill('Тест Проверка');
    const phoneInput = await firstForm.$('input[type="tel"]');
    await phoneInput.fill('+79991234567');
    await wait(300);
    await page.screenshot({ path: path.join(SCREENSHOTS, '07-form-filled.png'), fullPage: false });

    console.log('All screenshots saved to', SCREENSHOTS);
  } catch (e) {
    console.error('Error:', e.message);
    await page.screenshot({ path: path.join(SCREENSHOTS, 'error.png'), fullPage: false });
    process.exit(1);
  } finally {
    await browser.close();
  }
})();
