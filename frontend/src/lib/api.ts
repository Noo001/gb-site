export const API_BASE = (process.env.API_URL ?? "http://nginx:8000").replace(/\/$/, "");

export const API_PUBLIC_BASE = (
  process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000/api"
).replace(/\/$/, "");

export type Category = {
  id: number;
  name: string;
  slug: string;
  full_path: string;
  url: string;
  image: string;
  children: Category[];
  parent?: { id: number; name: string; url: string } | null;
};

export type Paginated<T> = {
  current_page: number;
  data: T[];
  first_page_url: string;
  from: number | null;
  last_page: number;
  last_page_url: string;
  links: { url: string | null; label: string; active: boolean }[];
  next_page_url: string | null;
  path: string;
  per_page: number;
  prev_page_url: string | null;
  to: number | null;
  total: number;
};

export type Product = {
  id: number;
  name: string;
  slug: string;
  sku: string | null;
  brand: string | null;
  description: string | null;
  url: string;
  category_id?: number | null;
  category?: { id: number; name: string; url: string; image?: string } | null;
  images?: string[];
  offers?: Offer[];
};

export type Offer = {
  id: number;
  name: string;
  sku: string | null;
  barcode: string | null;
};

export type User = {
  id: number;
  name: string;
  email: string | null;
  phone: string | null;
};

export type CartItem = {
  id: number;
  quantity: number;
  product: {
    id: number;
    name: string;
    slug: string;
    url: string;
    image: string;
  };
  offer: { id: number; name: string } | null;
};

export type WishlistItem = {
  id: number;
  product: {
    id: number;
    name: string;
    slug: string;
    url: string;
    image: string;
  };
  offer: { id: number; name: string } | null;
};

export type OrderItem = {
  id: number;
  product_id: number | null;
  offer_id: number | null;
  product_name: string;
  offer_name: string | null;
  quantity: number;
  price: number | null;
  total: number | null;
};

export type Order = {
  id: number;
  status: string;
  status_label: string;
  customer_name: string;
  customer_phone: string;
  customer_email: string | null;
  customer_city: string | null;
  customer_comment: string | null;
  manager_comment: string | null;
  total: number | null;
  items_count: number;
  created_at: string;
  items?: OrderItem[];
};

export async function fetchJson<T>(url: string): Promise<T> {
  const res = await fetch(url, { next: { revalidate: 60 } });
  if (!res.ok) {
    throw new Error(`Fetch error: ${res.status} ${res.statusText}`);
  }
  return (await res.json()) as T;
}

export async function getCategories(): Promise<Category[]> {
  const data = await fetchJson<{ data: Category[] }>(`${API_BASE}/api/categories`);
  return data.data;
}

export async function getCategory(path: string): Promise<Category | null> {
  const encoded = encodeURIComponent(path);
  const data = await fetchJson<{ data: Category }>(
    `${API_BASE}/api/categories/${encoded}`
  ).catch(() => null);
  return data?.data ?? null;
}

export async function getCategoryProducts(
  path: string,
  page = 1
): Promise<Paginated<Product>> {
  const encoded = encodeURIComponent(path);
  return fetchJson<Paginated<Product>>(
    `${API_BASE}/api/categories/${encoded}/products?page=${page}`
  );
}

export async function getProducts(
  searchParams?: Record<string, string>
): Promise<Paginated<Product>> {
  const qs = searchParams ? new URLSearchParams(searchParams).toString() : "";
  return fetchJson<Paginated<Product>>(`${API_BASE}/api/products?${qs}`);
}

export async function getProduct(slug: string): Promise<Product | null> {
  const data = await fetchJson<{ data: Product }>(
    `${API_BASE}/api/products/${encodeURIComponent(slug)}`
  ).catch(() => null);
  return data?.data ?? null;
}

export async function getSeo(path: string): Promise<{
  title?: string;
  description?: string;
  h1?: string;
  breadcrumbs?: { name: string; url: string }[];
}> {
  const data = await fetchJson<{
    title?: string;
    description?: string;
    h1?: string;
    breadcrumbs?: { name: string; url: string }[];
  }>(`${API_BASE}/api/seo?path=${encodeURIComponent(path)}`).catch(() => ({}));
  return data ?? {};
}

// Client-side helpers (run only in browser)
function getToken(): string | null {
  if (typeof window === "undefined") return null;
  return localStorage.getItem("gb-token");
}

function setToken(token: string | null) {
  if (typeof window === "undefined") return;
  if (token) localStorage.setItem("gb-token", token);
  else localStorage.removeItem("gb-token");
}

async function fetchAuth<T>(
  path: string,
  options: RequestInit = {}
): Promise<T> {
  const token = getToken();
  const res = await fetch(`${API_PUBLIC_BASE}${path}`, {
    ...options,
    credentials: "include",
    headers: {
      "Content-Type": "application/json",
      Accept: "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
  });

  const text = await res.text();
  let data: unknown = null;
  try {
    data = JSON.parse(text);
  } catch {
    data = { message: text };
  }

  if (!res.ok) {
    const message =
      (data as { message?: string })?.message ||
      (data as { error?: string })?.error ||
      `Ошибка ${res.status}`;
    throw new Error(message);
  }

  return data as T;
}

export function isAuthenticatedClient(): boolean {
  return typeof window !== "undefined" && !!localStorage.getItem("gb-token");
}

// Auth API
export async function apiRegister(payload: {
  name: string;
  email?: string;
  phone?: string;
  password: string;
  password_confirmation: string;
  privacy?: boolean;
}): Promise<{ user: User; token: string }> {
  const res = await fetchAuth<{ user: User; token: string }>("/register", {
    method: "POST",
    body: JSON.stringify(payload),
  });
  setToken(res.token);
  return res;
}

export async function apiLogin(payload: {
  login: string;
  password: string;
  remember?: boolean;
}): Promise<{ user: User; token: string }> {
  const res = await fetchAuth<{ user: User; token: string }>("/login", {
    method: "POST",
    body: JSON.stringify(payload),
  });
  setToken(res.token);
  return res;
}

export async function apiLogout(): Promise<void> {
  await fetchAuth("/logout", { method: "POST" });
  setToken(null);
}

export async function apiGetUser(): Promise<User> {
  const data = await fetchAuth<{ data: User }>("/user");
  return data.data;
}

// Cart API
export async function apiGetCart(): Promise<{ data: CartItem[]; count: number }> {
  return fetchAuth<{ data: CartItem[]; count: number }>("/cart");
}

export async function apiAddToCart(payload: {
  product_id?: number;
  offer_id?: number;
  quantity?: number;
}): Promise<{ data: CartItem[]; count: number }> {
  return fetchAuth<{ data: CartItem[]; count: number }>("/cart/items", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function apiUpdateCartItem(
  id: number,
  quantity: number
): Promise<{ data: CartItem[]; count: number }> {
  return fetchAuth<{ data: CartItem[]; count: number }>(`/cart/items/${id}`, {
    method: "PATCH",
    body: JSON.stringify({ quantity }),
  });
}

export async function apiRemoveCartItem(
  id: number
): Promise<{ data: CartItem[]; count: number }> {
  return fetchAuth<{ data: CartItem[]; count: number }>(`/cart/items/${id}`, {
    method: "DELETE",
  });
}

export async function apiClearCart(): Promise<{ data: CartItem[]; count: number }> {
  return fetchAuth<{ data: CartItem[]; count: number }>("/cart", {
    method: "DELETE",
  });
}

// Orders API
export async function apiCreateOrder(payload: {
  customer_name: string;
  customer_phone: string;
  customer_email?: string;
  customer_city?: string;
  customer_comment?: string;
}): Promise<{ data: Order; message: string }> {
  return fetchAuth<{ data: Order; message: string }>("/orders", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function apiGetOrders(): Promise<{ data: Order[] }> {
  return fetchAuth<{ data: Order[] }>("/orders");
}

export async function apiGetOrder(id: number): Promise<{ data: Order }> {
  return fetchAuth<{ data: Order }>(`/orders/${id}`);
}

// Wishlist API
export async function apiGetWishlist(): Promise<{
  data: WishlistItem[];
  count: number;
}> {
  return fetchAuth<{ data: WishlistItem[]; count: number }>("/wishlist");
}

export async function apiAddToWishlist(payload: {
  product_id?: number;
  offer_id?: number;
}): Promise<{ data: WishlistItem[]; count: number }> {
  return fetchAuth<{ data: WishlistItem[]; count: number }>("/wishlist", {
    method: "POST",
    body: JSON.stringify(payload),
  });
}

export async function apiRemoveFromWishlist(
  id: number
): Promise<{ data: WishlistItem[]; count: number }> {
  return fetchAuth<{ data: WishlistItem[]; count: number }>(`/wishlist/${id}`, {
    method: "DELETE",
  });
}
