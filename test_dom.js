const { chromium } = require('playwright');
(async () => {
  const browser = await chromium.launch({ headless: true });
  const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
  const page = await context.newPage();
  await page.goto('https://gbsale.ru/pc', { waitUntil: 'networkidle', timeout: 60000 });
  await page.waitForTimeout(2000);
  const info = await page.evaluate(() => {
    const svg = document.querySelector('.pc-case-svg');
    const front = document.querySelector('.pc-case-face--front');
    const scene = document.querySelector('.pc-case-scene');
    const cube = document.querySelector('.pc-case-3d');
    return {
      svgExists: !!svg,
      svgChildCount: svg ? svg.children.length : 0,
      svgHTML: svg ? svg.outerHTML.slice(0, 500) : null,
      svgDisplay: svg ? window.getComputedStyle(svg).display : null,
      svgVisibility: svg ? window.getComputedStyle(svg).visibility : null,
      svgRect: svg ? svg.getBoundingClientRect() : null,
      frontRect: front ? front.getBoundingClientRect() : null,
      frontDisplay: front ? window.getComputedStyle(front).display : null,
      frontVisibility: front ? window.getComputedStyle(front).visibility : null,
      cubeTransform: cube ? window.getComputedStyle(cube).transform : null,
    };
  });
  console.log(JSON.stringify(info, null, 2));
  await page.screenshot({ path: 'C:/repos/gb-site/pc3d_dom.png', fullPage: false });
  await browser.close();
})();
