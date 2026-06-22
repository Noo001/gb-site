"use client";

import { useEffect } from "react";
import { useAuthStore } from "@/stores/authStore";
import { useCartStore } from "@/stores/cartStore";

export default function AuthProvider({
  children,
}: {
  children: React.ReactNode;
}) {
  const init = useAuthStore((s) => s.init);
  const token = useAuthStore((s) => s.token);
  const loadCart = useCartStore((s) => s.load);

  useEffect(() => {
    init();
  }, [init]);

  useEffect(() => {
    loadCart();
  }, [token, loadCart]);

  return <>{children}</>;
}
