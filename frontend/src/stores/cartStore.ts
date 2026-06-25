import { create } from "zustand";
import {
  apiAddToCart,
  apiClearCart,
  apiGetCart,
  apiRemoveCartItem,
  apiUpdateCartItem,
  type CartItem,
} from "@/lib/api";

interface CartState {
  items: CartItem[];
  count: number;
  isLoading: boolean;
  load: () => Promise<void>;
  add: (payload: {
    product_id?: number;
    offer_id?: number;
    quantity?: number;
  }) => Promise<void>;
  updateQty: (id: number, quantity: number) => Promise<void>;
  remove: (id: number) => Promise<void>;
  clear: () => Promise<void>;
}

export const useCartStore = create<CartState>((set) => ({
  items: [],
  count: 0,
  isLoading: false,

  load: async () => {
    set({ isLoading: true });
    try {
      const res = await apiGetCart();
      set({ items: res.data, count: res.count });
    } finally {
      set({ isLoading: false });
    }
  },

  add: async (payload) => {
    const res = await apiAddToCart(payload);
    set({ items: res.data, count: res.count });
  },

  updateQty: async (id, quantity) => {
    const res = await apiUpdateCartItem(id, quantity);
    set({ items: res.data, count: res.count });
  },

  remove: async (id) => {
    const res = await apiRemoveCartItem(id);
    set({ items: res.data, count: res.count });
  },

  clear: async () => {
    const res = await apiClearCart();
    set({ items: res.data, count: res.count });
  },
}));
