import { create } from "zustand";
import { persist } from "zustand/middleware";

interface CityState {
  city: string;
  detected: boolean;
  setCity: (city: string) => void;
  setDetected: (detected: boolean) => void;
}

export const useCityStore = create<CityState>()(
  persist(
    (set) => ({
      city: "Воронеж",
      detected: false,
      setCity: (city) => set({ city }),
      setDetected: (detected) => set({ detected }),
    }),
    { name: "gb-city" }
  )
);
