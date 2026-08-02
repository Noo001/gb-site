const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();
  await page.goto('https://gbsale.ru/pc', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(2000);
  const info = await page.evaluate(() => {
    const svg = document.querySelector('.pc-case-svg');
    const caseBody = svg.querySelector('.pc-case-body');
    const mb = svg.querySelector('.pc-mb');
    const glass = svg.querySelector('.pc-glass');
    const caseGroup = svg.querySelector('.pc-slot-case');
    return {
      svgOpacity: window.getComputedStyle(svg).opacity,
      svgFill: window.getComputedStyle(svg).fill,
      svgColor: window.getComputedStyle(svg).color,
      caseBodyFill: caseBody ? window.getComputedStyle(caseBody).fill : null,
      caseBodyStroke: caseBody ? window.getComputedStyle(caseBody).stroke : null,
      caseBodyOpacity: caseBody ? window.getComputedStyle(caseBody).opacity : null,
      caseBodyVisibility: caseBody ? window.getComputedStyle(caseBody).visibility : null,
      caseGroupDisplay: caseGroup ? window.getComputedStyle(caseGroup).display : null,
      caseGroupVisibility: caseGroup ? window.getComputedStyle(caseGroup).visibility : null,
      mbFill: mb ? window.getComputedStyle(mb).fill : null,
      mbStroke: mb ? window.getComputedStyle(mb).stroke : null,
      glassFill: glass ? window.getComputedStyle(glass).fill : null,
      glassOpacity: glass ? window.getComputedStyle(glass).opacity : null,
      caseBodyRect: caseBody ? caseBody.getBoundingClientRect() : null,
    };
  });
  console.log(JSON.stringify(info, null, 2));
  await browser.close();
})();
