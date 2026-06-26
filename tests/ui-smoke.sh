#!/usr/bin/env bash
set -euo pipefail

BASE="${BASE_URL:-http://localhost:3000}"
ORIGINAL="${ORIGINAL_URL:-https://gadget-bar.ru}"
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT

pass=0
fail=0

ok() { echo "  ✓ $1"; ((pass++)) || true; }
err() { echo "  ✗ $1"; ((fail++)) || true; }

echo "=== UI smoke tests ==="
echo "Frontend: $BASE"
echo "Original: $ORIGINAL"
echo

# 1. Status codes for main routes
echo "1. Page status codes"
for path in / /brands /brands/apple /blog /sales /installment /contacts /info/payment /info/delivery /info/warranty /info/return /catalog/apple /catalog/gadzhety /product/besprovodnye-naushniki-apple-airpods-3-lightning-case; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "${BASE}${path}")
  if [[ "$code" == "200" ]]; then
    ok "$path -> $code"
  else
    err "$path -> $code"
  fi
done

# Admin route should redirect to login (302) or serve login (200)
admin_code=$(curl -sL -o /dev/null -w "%{http_code}" "${BASE}/admin")
if [[ "$admin_code" =~ ^(200|302)$ ]]; then
  ok "/admin -> $admin_code"
else
  err "/admin -> $admin_code"
fi

# 2. Mega-menu presence
echo
echo "2. Mega-menu categories on our site"
curl -sL "$BASE/" > "$TMP/ours.html"
for name in "Apple" "Samsung" "Dyson" "Смартфоны" "Игровые консоли" "Наушники и аудио" "SMEG" "Аксессуары" "Гаджеты"; do
  if grep -q "$name" "$TMP/ours.html"; then
    ok "menu contains: $name"
  else
    err "menu missing: $name"
  fi
done

# 3. Compare with original site
echo
echo "3. Comparison with original site menu"
curl -sL "$ORIGINAL/" > "$TMP/orig.html"
for name in "Apple" "Samsung" "Dyson" "Смартфоны" "Игровые консоли" "Наушники и аудио" "SMEG" "Аксессуары" "Гаджеты"; do
  orig=0
  ours=0
  grep -q "$name" "$TMP/orig.html" && orig=1 || true
  grep -q "$name" "$TMP/ours.html" && ours=1 || true
  if [[ "$orig" == "1" && "$ours" == "1" ]]; then
    ok "$name present on both sites"
  elif [[ "$orig" == "1" && "$ours" == "0" ]]; then
    err "$name present on original but missing on ours"
  elif [[ "$orig" == "0" && "$ours" == "1" ]]; then
    ok "$name added on ours"
  else
    err "$name missing on both sites"
  fi
done

# 4. Product tabs
echo
echo "4. Product page tabs"
curl -sL "$BASE/product/besprovodnye-naushniki-apple-airpods-3-lightning-case" > "$TMP/product.html"
for tab in "Описание" "Характеристики" "Оплата" "Доставка"; do
  if grep -q "$tab" "$TMP/product.html"; then
    ok "tab present: $tab"
  else
    err "tab missing: $tab"
  fi
done

# 5. Top navigation links
echo
echo "5. Top navigation pages"
for path in /brands /blog /sales /installment /contacts /info/payment; do
  code=$(curl -s -o /dev/null -w "%{http_code}" "${BASE}${path}")
  if [[ "$code" == "200" ]]; then
    ok "$path -> $code"
  else
    err "$path -> $code"
  fi
done

# 6. Category page has children after tree rebuild
echo
echo "6. Category page subcategories"
curl -sL "$BASE/catalog/gadzhety" > "$TMP/gadzhety.html"
for name in "Для дома" "Смарт-часы" "Все для авто"; do
  if grep -q "$name" "$TMP/gadzhety.html"; then
    ok "gadzhety contains: $name"
  else
    err "gadzhety missing: $name"
  fi
done

# 7. Social links: presence, shape and contacts page
echo
echo "7. Social links unified (rounded-2xl)"
curl -sL "$BASE/contacts" > "$TMP/contacts.html"
for url in "https://vk.com/gadgetbarru" "https://t.me/GadgetBarVrn_Bot" "https://max.ru/gadget_bar_ru"; do
  if ! grep -q "$url" "$TMP/ours.html"; then
    err "social link missing on homepage: $url"
  elif ! grep -q "$url" "$TMP/contacts.html"; then
    err "social link missing on contacts: $url"
  else
    class=$(grep -oE "href=\"$url\"[^>]*class=\"[^\"]*\"" "$TMP/ours.html" | head -n 1)
    if echo "$class" | grep -q "rounded-2xl"; then
      ok "social link rounded-2xl: $url"
    else
      err "social link is not rounded-2xl: $url (class: $class)"
    fi
  fi
done

# 8. Product images fallback on catalog and product pages
echo
echo "8. Product images fallback"
curl -sL "$BASE/catalog/gadzhety" > "$TMP/gadzhety.html"
if grep -q '<img' "$TMP/gadzhety.html"; then
  ok "catalog page contains product images"
else
  err "catalog page has no product images"
fi

if grep -q '<img' "$TMP/product.html"; then
  ok "product page contains an image"
else
  err "product page has no image"
fi

if ! grep -q "Нет изображения" "$TMP/product.html"; then
  ok "product page has no 'Нет изображения' placeholder"
else
  err "product page still shows 'Нет изображения'"
fi

# 9. Brand logos imported (only Pitaca may stay without logo)
echo
echo "9. Brand logos"
brand_stats=$(docker compose exec -T api php -r '
require "vendor/autoload.php";
$app = require_once "bootstrap/app.php";
$app->make("Illuminate\\Contracts\\Console\\Kernel")->bootstrap();
$total = App\Models\Category::where("url", "like", "/brands/%")->count();
$with = App\Models\Category::where("url", "like", "/brands/%")->whereHas("media")->count();
echo "$with/$total";
')
if [[ "$brand_stats" =~ ^([0-9]+)/([0-9]+)$ ]]; then
  with="${BASH_REMATCH[1]}"
  total="${BASH_REMATCH[2]}"
  missing=$((total - with))
  if [[ "$missing" -le 1 ]]; then
    ok "brand logos: $with/$total (missing: $missing)"
  else
    err "too many brands without logo: $with/$total"
  fi
else
  err "could not fetch brand stats: $brand_stats"
fi

# 10. Content pages have images
echo
echo "10. Content pages images"
curl -sL "$BASE/sales" > "$TMP/sales.html"
if grep -q "images/original/promos" "$TMP/sales.html"; then
  ok "sales page has promo images"
else
  err "sales page has no promo images"
fi

curl -sL "$BASE/blog" > "$TMP/blog.html"
if grep -q "images/original/blog" "$TMP/blog.html"; then
  ok "blog page has article images"
else
  err "blog page has no article images"
fi

curl -sL "$BASE/installment" > "$TMP/installment.html"
if grep -q "images/original/promos" "$TMP/installment.html"; then
  ok "installment page has promo image"
else
  err "installment page has no promo image"
fi

echo
echo "=== Results: $pass passed, $fail failed ==="

if [[ "$fail" -gt 0 ]]; then
  exit 1
fi
