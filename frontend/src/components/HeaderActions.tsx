"use client";

import Link from "next/link";
import { useState, useEffect } from "react";
import { useAuthStore } from "@/stores/authStore";
import { useCartStore } from "@/stores/cartStore";
import { useWishlistStore } from "@/stores/wishlistStore";
import AuthModal from "./AuthModal";

function UserIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="22"
      height="22"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
      <circle cx="12" cy="7" r="4" />
    </svg>
  );
}

function HeartIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="22"
      height="22"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
    </svg>
  );
}

function CartIcon({ className }: { className?: string }) {
  return (
    <svg
      className={className}
      width="22"
      height="22"
      viewBox="0 0 24 24"
      fill="none"
      stroke="currentColor"
      strokeWidth="2"
      strokeLinecap="round"
      strokeLinejoin="round"
    >
      <circle cx="9" cy="21" r="1" />
      <circle cx="20" cy="21" r="1" />
      <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6" />
    </svg>
  );
}

export default function HeaderActions() {
  const [isAuthOpen, setIsAuthOpen] = useState(false);
  const user = useAuthStore((s) => s.user);
  const logout = useAuthStore((s) => s.logout);
  const cartCount = useCartStore((s) => s.count);
  const wishlistCount = useWishlistStore((s) => s.count);
  const loadWishlist = useWishlistStore((s) => s.load);

  useEffect(() => {
    if (user) loadWishlist();
  }, [user, loadWishlist]);

  return (
    <>
      <div className="flex items-center gap-5">
        {user ? (
          <div className="group relative">
            <button className="flex items-center gap-1.5 text-sm hover:text-[var(--accent)]">
              <UserIcon />
              <span className="hidden lg:inline">{user.name}</span>
            </button>
            <div className="absolute right-0 top-full z-30 hidden min-w-[160px] pt-2 group-hover:block">
              <div className="rounded-xl border border-[var(--border)] bg-white py-2 shadow-xl">
                <Link
                  href="/wishlist"
                  className="block px-4 py-2 text-sm text-[var(--text-muted)] hover:bg-gray-50 hover:text-[var(--accent)]"
                >
                  Избранное
                </Link>
                <button
                  onClick={() => logout()}
                  className="block w-full px-4 py-2 text-left text-sm text-[var(--text-muted)] hover:bg-gray-50 hover:text-[var(--accent)]"
                >
                  Выйти
                </button>
              </div>
            </div>
          </div>
        ) : (
          <button
            onClick={() => setIsAuthOpen(true)}
            className="flex items-center gap-1.5 text-sm hover:text-[var(--accent)]"
          >
            <UserIcon />
            <span className="hidden lg:inline">Войти</span>
          </button>
        )}

        <Link href="/wishlist" className="relative hover:text-[var(--accent)]">
          <HeartIcon />
          {wishlistCount > 0 && (
            <span className="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-[var(--accent)] text-[10px] font-bold text-white">
              {wishlistCount > 9 ? "9+" : wishlistCount}
            </span>
          )}
        </Link>

        <Link href="/cart" className="relative hover:text-[var(--accent)]">
          <CartIcon />
          {cartCount > 0 && (
            <span className="absolute -right-2 -top-2 flex h-4 w-4 items-center justify-center rounded-full bg-[var(--accent)] text-[10px] font-bold text-white">
              {cartCount > 9 ? "9+" : cartCount}
            </span>
          )}
        </Link>
      </div>

      <AuthModal isOpen={isAuthOpen} onClose={() => setIsAuthOpen(false)} />
    </>
  );
}
