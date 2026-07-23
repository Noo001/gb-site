#!/usr/bin/env node
const { URL } = require('url');
const pathLib = require('path');

const BASE = process.env.BASE_URL || 'http://localhost:3000';
const MAX_PAGES = Number(process.env.MAX_PAGES || '200');
const CONCURRENCY = Number(process.env.CONCURRENCY || '5');
const FETCH_TIMEOUT = Number(process.env.FETCH_TIMEOUT || '15000');

const SKIP_EXTENSIONS = new Set([
  '.css', '.js', '.ico', '.png', '.jpg', '.jpeg', '.webp', '.gif', '.svg',
  '.woff', '.woff2', '.ttf', '.eot', '.pdf', '.zip', '.mp4', '.webm',
]);

function shouldSkipPath(pathname) {
  const ext = pathLib.extname(pathname).toLowerCase();
  return SKIP_EXTENSIONS.has(ext);
}

const sleep = (ms) => new Promise((r) => setTimeout(r, ms));

async function fetchWithTimeout(url, options = {}) {
  const controller = new AbortController();
  const id = setTimeout(() => controller.abort(), FETCH_TIMEOUT);
  try {
    const res = await fetch(url, { ...options, signal: controller.signal });
    clearTimeout(id);
    return res;
  } catch (e) {
    clearTimeout(id);
    throw e;
  }
}

async function fetchHtml(url) {
  try {
    const res = await fetchWithTimeout(url, { redirect: 'follow' });
    const contentType = res.headers.get('content-type') || '';
    const text = contentType.includes('text/html') ? await res.text() : '';
    return { status: res.status, text, contentType };
  } catch (e) {
    return { status: 0, text: '', error: e.message };
  }
}

async function checkUrl(url) {
  try {
    let res;
    try {
      res = await fetchWithTimeout(url, { method: 'HEAD', redirect: 'follow', headers: { Accept: '*/*' } });
    } catch {
      res = null;
    }
    // Some servers do not support HEAD on dynamic routes; fallback to GET.
    if (!res || res.status === 405 || res.status === 501 || res.status === 0) {
      res = await fetchWithTimeout(url, { method: 'GET', redirect: 'follow', headers: { Accept: '*/*' } });
    }
    return { status: res.status, contentType: res.headers.get('content-type') || '' };
  } catch (e) {
    return { status: 0, error: e.message };
  }
}

function extractLinks(html, baseUrl) {
  const links = new Set();
  const regex = /href="([^"]+)"/g;
  let m;
  while ((m = regex.exec(html))) {
    try {
      const u = new URL(m[1], baseUrl);
      if (u.origin === new URL(baseUrl).origin && !shouldSkipPath(u.pathname)) {
        links.add(u.pathname + u.search);
      }
    } catch {}
  }
  return Array.from(links);
}

function extractImages(html, baseUrl) {
  const images = [];
  const regex = /<img[^>]+src="([^"]+)"/g;
  let m;
  while ((m = regex.exec(html))) {
    try {
      const u = new URL(m[1], baseUrl);
      images.push(u.href);
    } catch {}
  }
  return images;
}

async function crawl(start) {
  const visited = new Map(); // path -> {status, title, text, links, images, error}
  const queue = [start];
  const queued = new Set([start]);
  let idx = 0;

  while (idx < queue.length && visited.size < MAX_PAGES) {
    const batch = queue.slice(idx, idx + CONCURRENCY);
    idx += batch.length;

    await Promise.all(
      batch.map(async (path) => {
        if (visited.has(path)) return;
        const url = new URL(path, BASE).href;
        const { status, text, error } = await fetchHtml(url);
        const title = text.match(/<title>([^<]*)<\/title>/)?.[1]?.trim() || '';
        const links = status === 200 ? extractLinks(text, url) : [];
        const images = status === 200 ? extractImages(text, url) : [];
        visited.set(path, { status, title, text, links, images, error });

        for (const link of links) {
          if (!visited.has(link) && !queued.has(link)) {
            queued.add(link);
            queue.push(link);
          }
        }
      })
    );
  }
  return visited;
}

async function main() {
  console.log(`Crawling ${BASE} (max ${MAX_PAGES} pages)...`);
  const pages = await crawl('/');

  const brokenPages = [];
  const allInternalLinks = new Map(); // link -> from pages
  const pagesWithoutImages = [];
  const placeholderPages = [];

  for (const [path, data] of pages) {
    if (data.status !== 200) {
      brokenPages.push({ path, status: data.status, title: data.title });
    }
    if (data.status === 200 && data.images.length === 0) {
      pagesWithoutImages.push(path);
    }
    if (data.status === 200 && (data.text.includes('Нет фото') || data.text.includes('Нет изображения'))) {
      placeholderPages.push(path);
    }
    for (const link of data.links || []) {
      if (!allInternalLinks.has(link)) allInternalLinks.set(link, []);
      allInternalLinks.get(link).push(path);
    }
  }

  console.log(`\nChecked ${pages.size} pages.`);
  console.log(`Broken pages (non-200): ${brokenPages.length}`);
  for (const x of brokenPages.slice(0, 30)) {
    console.log(`  ${x.status} ${x.path} | ${x.title}`);
  }

  // Check all extracted internal links for 404
  console.log(`\nChecking ${allInternalLinks.size} unique internal links found on crawled pages...`);
  const brokenLinks = [];
  let checked = 0;
  const linkArr = Array.from(allInternalLinks.keys());
  for (let i = 0; i < linkArr.length; i += CONCURRENCY) {
    const batch = linkArr.slice(i, i + CONCURRENCY);
    await Promise.all(
      batch.map(async (link) => {
        const url = new URL(link, BASE).href;
        const res = await checkUrl(url);
        checked++;
        if (res.status !== 200) {
          brokenLinks.push({ link, status: res.status, from: allInternalLinks.get(link).slice(0, 3) });
        }
      })
    );
    if (checked % 50 === 0) process.stdout.write('.');
  }
  process.stdout.write('\n');

  console.log(`Broken internal links: ${brokenLinks.length}`);
  for (const x of brokenLinks.slice(0, 30)) {
    console.log(`  ${x.status} ${x.link} (found on: ${x.from.join(', ')})`);
  }
  if (brokenLinks.length > 30) console.log(`  ... and ${brokenLinks.length - 30} more`);

  // Check images
  const allImages = new Set();
  for (const data of pages.values()) {
    for (const img of data.images) allImages.add(img);
  }
  console.log(`\nChecking ${allImages.size} unique images...`);
  const brokenImages = [];
  checked = 0;
  const imgArr = Array.from(allImages);
  for (let i = 0; i < imgArr.length; i += CONCURRENCY) {
    const batch = imgArr.slice(i, i + CONCURRENCY);
    await Promise.all(
      batch.map(async (img) => {
        const res = await checkUrl(img);
        checked++;
        if (res.status !== 200 || !res.contentType.startsWith('image')) {
          brokenImages.push({ url: img, status: res.status, contentType: res.contentType, error: res.error });
        }
      })
    );
    if (checked % 50 === 0) process.stdout.write('.');
  }
  process.stdout.write('\n');

  console.log(`Broken images: ${brokenImages.length}`);
  for (const x of brokenImages.slice(0, 30)) {
    console.log(`  ${x.status} ${x.contentType} ${x.url}${x.error ? ' | ' + x.error : ''}`);
  }
  if (brokenImages.length > 30) console.log(`  ... and ${brokenImages.length - 30} more`);

  console.log(`\nPages without any <img>: ${pagesWithoutImages.length}`);
  for (const p of pagesWithoutImages.slice(0, 20)) console.log(`  ${p}`);

  console.log(`\nPages with image placeholders: ${placeholderPages.length}`);
  for (const p of placeholderPages.slice(0, 30)) console.log(`  ${p}`);

  process.exit(brokenPages.length + brokenLinks.length + brokenImages.length > 0 ? 1 : 0);
}

main();
