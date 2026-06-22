import { create } from "zustand";
import {
  apiAddToWishlist,
  apiGetWishlist,
  apiRemoveFromWishlist,
  type WishlistItem,
} from "@/lib/api";

interface WishlistState {
  items: WishlistItem[];
  count: number;
  isLoading: boolean;
  load: () => Promise<void>;
  add: (payload: { product_id?: number; offer_id?: number }) => Promise<void>;
  remove: (id: number) => Promise<void>;
  toggle: (productId: number) => Promise<void>;
}

export const useWishlistStore = create<WishlistState>((set, get) => ({
  items: [],
  count: 0,
  isLoading: false,

  load: async () => {
    set({ isLoading: true });
    try {
      const res = await apiGetWishlist();
      set({ items: res.data, count: res.count });
    } finally {
      set({ isLoading: false });
    }
  },

  add: async (payload) => {
    const res = await apiAddToWishlist(payload);
    set({ items: res.data, count: res.count });
  },

  remove: async (id) => {
    const res = await apiRemoveFromWishlist(id);
    set({ items: res.data, count: res.count });
  },

  toggle: async (productId) => {
    const existing = get().items.find((i) => i.product.id === productId);
    if (existing) {
      await get().remove(existing.id);
    } else {
      await get().add({ product_id: productId });
    }
  },
}));
