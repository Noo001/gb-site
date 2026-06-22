#!/usr/bin/env bash
set -euo pipefail

API="${API_URL:-http://localhost:8000/api}"
TMP=$(mktemp -d)
trap 'rm -rf "$TMP"' EXIT

pass=0
fail=0

ok() { echo "  ✓ $1"; ((pass++)) || true; }
err() { echo "  ✗ $1"; ((fail++)) || true; }

echo "=== API functional tests ==="
echo "API: $API"
echo

# 1. Register
EMAIL="test$(date +%s)@example.com"
PHONE="+7999$(date +%s | tail -c 8)"
echo "1. Register"
REGISTER=$(curl -s -X POST "$API/register" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"name\":\"Test\",\"email\":\"$EMAIL\",\"phone\":\"$PHONE\",\"password\":\"password\",\"password_confirmation\":\"password\",\"privacy\":true}")
TOKEN=$(echo "$REGISTER" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4)
if [[ -n "$TOKEN" ]]; then
  ok "register -> token received"
else
  err "register failed: $REGISTER"
fi

# 2. Login
echo
echo "2. Login"
LOGIN=$(curl -s -X POST "$API/login" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d "{\"login\":\"$EMAIL\",\"password\":\"password\"}")
TOKEN=$(echo "$LOGIN" | grep -o '"token":"[^"]*"' | head -1 | cut -d'"' -f4)
if [[ -n "$TOKEN" ]]; then
  ok "login -> token received"
else
  err "login failed: $LOGIN"
fi

# 3. Current user
echo
echo "3. Current user"
USER=$(curl -s "$API/user" -H "Accept: application/json" -H "Authorization: Bearer $TOKEN")
if echo "$USER" | grep -q "$EMAIL"; then
  ok "/user returns email"
else
  err "/user failed: $USER"
fi

# 4. Cart as guest
echo
echo "4. Guest cart"
CART=$(curl -s -X POST "$API/cart/items" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"product_id":424,"quantity":1}')
if echo "$CART" | grep -q '"count":1'; then
  ok "guest add to cart -> count 1"
else
  err "guest cart failed: $CART"
fi

# 5. Wishlist requires auth
echo
echo "5. Wishlist auth protection"
WISH=$(curl -s -X POST "$API/wishlist" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"product_id":424}')
if echo "$WISH" | grep -qi 'unauth\|зарегистрируйтесь\|Unauthorized'; then
  ok "wishlist without token rejected"
else
  err "wishlist did not reject guest: $WISH"
fi

# 6. Wishlist with auth
echo
echo "6. Wishlist with auth"
WISH=$(curl -s -X POST "$API/wishlist" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer $TOKEN" \
  -d '{"product_id":424}')
if echo "$WISH" | grep -q '"count":1'; then
  ok "auth add to wishlist -> count 1"
else
  err "auth wishlist failed: $WISH"
fi

# 7. Logout
echo
echo "7. Logout"
LOGOUT=$(curl -s -X POST "$API/logout" -H "Accept: application/json" -H "Authorization: Bearer $TOKEN")
if echo "$LOGOUT" | grep -q '"message"'; then
  ok "logout success"
else
  err "logout failed: $LOGOUT"
fi

echo
echo "=== Results: $pass passed, $fail failed ==="

if [[ "$fail" -gt 0 ]]; then
  exit 1
fi
