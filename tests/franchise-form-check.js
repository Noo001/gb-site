const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const URL = 'https://fr.gbsale.ru';
const SCREENSHOTS = path.join(__dirname, 'screenshots', 'franchise-form-' + Date.now());
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

  // Capture network requests
  let submitResponse = null;
  page.on('response', async (res) => {
    const req = res.request();
    if (req.url().includes('/submit') && req.method() === 'POST') {
      submitResponse = { status: res.status(), body: await res.text().catch(() => '') };
    }
  });

  try {
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await wait(1500);

    // Scroll to forms
    await page.evaluate(() => document.getElementById('forms').scrollIntoView({ behavior: 'instant' }));
    await wait(500);

    // Fill callback form (second card)
    const forms = await page.$$('#forms .fr-form-card');
    console.log('Found form cards:', forms.length);
    const callbackForm = forms[1];
    const inputs = await callbackForm.$$('input, textarea');
    await inputs[0].fill('Тест Проверка');
    await inputs[1].fill('+79991234567');
    await inputs[2].fill('Удобно после 18:00');

    await callbackForm.screenshot({ path: path.join(SCREENSHOTS, 'callback-filled.png') });

    // Submit callback form
    const submitBtn = await callbackForm.$('button[type="submit"]');
    await submitBtn.click();
    await wait(2500);

    await callbackForm.screenshot({ path: path.join(SCREENSHOTS, 'callback-submitted.png') });

    if (submitResponse) {
      console.log('Submit response status:', submitResponse.status);
      console.log('Submit response body:', submitResponse.body.substring(0, 200));
    } else {
      console.log('No submit response captured');
    }

    // Check for success message
    const successText = await callbackForm.$eval('.fr-alert-success', el => el.textContent).catch(() => null);
    const errorText = await callbackForm.$eval('.fr-alert-error', el => el.textContent).catch(() => null);

    if (successText) {
      console.log('SUCCESS:', successText);
    } else if (errorText) {
      console.log('ERROR:', errorText);
    } else {
      console.log('No success/error message found');
    }

    // Mobile check
    await page.setViewportSize({ width: 390, height: 844 });
    await page.goto(URL, { waitUntil: 'networkidle', timeout: 60000 });
    await wait(1500);
    await page.screenshot({ path: path.join(SCREENSHOTS, 'mobile-hero.png'), fullPage: false });

    await page.evaluate(() => document.getElementById('formats').scrollIntoView({ behavior: 'instant' }));
    await wait(500);
    await page.screenshot({ path: path.join(SCREENSHOTS, 'mobile-formats.png'), fullPage: false });

    console.log('Screenshots saved to', SCREENSHOTS);
  } catch (e) {
    console.error('Error:', e.message);
    await page.screenshot({ path: path.join(SCREENSHOTS, 'error.png'), fullPage: false });
    process.exit(1);
  } finally {
    await browser.close();
  }
})();
