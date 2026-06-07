"use client";

import { QuoteProvider } from "./context/QuoteContext";

export function Providers({ children }: { children: React.ReactNode }) {
  return <QuoteProvider>{children}</QuoteProvider>;
}
