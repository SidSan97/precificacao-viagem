"use client";

import Link from "next/link";
import { useRouter } from "next/navigation";
import { useEffect } from "react";

import CardResult from "../components/cardResult";
import { useQuoteContext } from "../context/QuoteContext";

export default function ResultadoPage() {
  const router = useRouter();
  const { result } = useQuoteContext();

  useEffect(() => {
    if (!result) {
      router.replace("/");
    }
  }, [result, router]);

  if (!result) {
    return (
      <div className="min-h-full bg-zinc-50 px-4 py-10">
        <main className="mx-auto w-full max-w-3xl text-sm text-zinc-600">
          Redirecionando...
        </main>
      </div>
    );
  }

  return (
    <div className="min-h-full bg-zinc-50 px-4 py-10">
      <main className="mx-auto flex w-full max-w-3xl flex-col gap-8">
        <header className="space-y-2">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <h1 className="text-3xl font-semibold tracking-tight text-zinc-900">
              Resultado da cotação
            </h1>
            <div className="flex flex-wrap gap-2">
              <Link
                href="/"
                className="rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
              >
                Nova cotação
              </Link>
              <Link
                href="/listar-cotacoes"
                className="rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
              >
                Ver cotações salvas
              </Link>
            </div>
          </div>
          <p className="text-sm text-zinc-600">
            Confira abaixo o detalhamento completo da cotação calculada.
          </p>
        </header>

        <CardResult result={result} />
      </main>
    </div>
  );
}
