import { create } from "zustand";
import { persist } from "zustand/middleware";
import {
  apiGetUser,
  apiLogin,
  apiLogout,
  apiRegister,
  type User,
} from "@/lib/api";

interface AuthState {
  user: User | null;
  token: string | null;
  isLoading: boolean;
  setAuth: (user: User, token: string) => void;
  clearAuth: () => void;
  init: () => Promise<void>;
  login: (payload: {
    login: string;
    password: string;
    remember?: boolean;
  }) => Promise<void>;
  register: (payload: {
    name: string;
    email?: string;
    phone?: string;
    password: string;
    password_confirmation: string;
    privacy?: boolean;
  }) => Promise<void>;
  logout: () => Promise<void>;
}

export const useAuthStore = create<AuthState>()(
  persist(
    (set, get) => ({
      user: null,
      token: null,
      isLoading: true,

      setAuth: (user, token) => {
        if (typeof window !== "undefined") {
          localStorage.setItem("gb-token", token);
        }
        set({ user, token });
      },

      clearAuth: () => {
        if (typeof window !== "undefined") {
          localStorage.removeItem("gb-token");
        }
        set({ user: null, token: null, isLoading: false });
      },

      init: async () => {
        let token = get().token;
        if (!token && typeof window !== "undefined") {
          token = localStorage.getItem("gb-token");
          if (token) set({ token });
        }
        if (!token) {
          set({ isLoading: false });
          return;
        }
        try {
          const user = await apiGetUser();
          set({ user, isLoading: false });
        } catch {
          set({ user: null, token: null, isLoading: false });
        }
      },

      login: async (payload) => {
        const res = await apiLogin(payload);
        if (typeof window !== "undefined") {
          localStorage.setItem("gb-token", res.token);
        }
        set({ user: res.user, token: res.token, isLoading: false });
      },

      register: async (payload) => {
        const res = await apiRegister(payload);
        if (typeof window !== "undefined") {
          localStorage.setItem("gb-token", res.token);
        }
        set({ user: res.user, token: res.token, isLoading: false });
      },

      logout: async () => {
        try {
          await apiLogout();
        } finally {
          if (typeof window !== "undefined") {
            localStorage.removeItem("gb-token");
          }
          set({ user: null, token: null, isLoading: false });
        }
      },
    }),
    {
      name: "gb-auth",
      partialize: (state) => ({ token: state.token }),
    }
  )
);
