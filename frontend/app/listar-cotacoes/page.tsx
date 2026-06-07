"use client";

import Link from "next/link";
import { useEffect, useState } from "react";

import { fetchQuotes } from "../hookies/quote-api";
import type { Adicional, Destino, StoredQuote } from "../hookies/types";

const DESTINO_LABELS: Record<Destino, string> = {
  NACIONAL: "Nacional",
  AMERICAS: "Américas",
  EUROPA: "Europa",
};

const ADICIONAL_LABELS: Record<Adicional, string> = {
  BAGAGEM: "Bagagem",
  ESPORTES_AVENTURA: "Esportes de aventura",
};

function formatBRL(value: string | number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(Number(value));
}

function formatDate(value: string): string {
  return new Intl.DateTimeFormat("pt-BR").format(new Date(`${value}T00:00:00`));
}

function formatDateTime(value: string): string {
  return new Intl.DateTimeFormat("pt-BR", {
    dateStyle: "short",
    timeStyle: "short",
  }).format(new Date(value));
}

function quoteKey(quote: StoredQuote, index: number): string {
  return `${quote.created_at}-${quote.data_inicio}-${index}`;
}

function QuoteDetail({ quote }: { quote: StoredQuote }) {
  return (
    <div className="space-y-4 border-t border-zinc-200 px-5 py-4">
      <div className="grid gap-3 rounded-xl bg-zinc-50 p-4 text-sm sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <p className="text-zinc-500">Destino</p>
          <p className="font-medium text-zinc-900">{DESTINO_LABELS[quote.destino]}</p>
        </div>
        <div>
          <p className="text-zinc-500">Data fim</p>
          <p className="font-medium text-zinc-900">{formatDate(quote.data_fim)}</p>
        </div>
        <div>
          <p className="text-zinc-500">Dias cobrados</p>
          <p className="font-medium text-zinc-900">{quote.dias_cobrados}</p>
        </div>
        <div>
          <p className="text-zinc-500">Cotado em</p>
          <p className="font-medium text-zinc-900">{formatDateTime(quote.created_at)}</p>
        </div>
      </div>

      <div className="space-y-3">
        <h3 className="text-sm font-medium text-zinc-900">Viajantes</h3>
        {quote.viajantes.map((viajante) => (
          <article
            key={`${viajante.nome}-${viajante.data_nascimento}`}
            className="rounded-xl border border-zinc-200 p-4"
          >
            <div className="flex flex-wrap items-start justify-between gap-2">
              <div>
                <p className="font-medium text-zinc-900">{viajante.nome}</p>
                <p className="text-sm text-zinc-500">
                  Nascimento: {formatDate(viajante.data_nascimento)}
                </p>
              </div>
              <p className="font-medium text-zinc-900">
                {formatBRL(viajante.subtotal)}
              </p>
            </div>

            {viajante.adicionais_aplicados.length > 0 && (
              <p className="mt-2 text-sm text-zinc-600">
                Adicionais:{" "}
                {viajante.adicionais_aplicados
                  .map((adicional) => ADICIONAL_LABELS[adicional])
                  .join(", ")}
              </p>
            )}

            {viajante.avisos.length > 0 && (
              <ul className="mt-3 list-disc space-y-1 pl-5 text-sm text-amber-800">
                {viajante.avisos.map((aviso) => (
                  <li key={aviso.aviso}>{aviso.aviso}</li>
                ))}
              </ul>
            )}
          </article>
        ))}
      </div>
    </div>
  );
}

export default function ListarCotacoes() {
  const [quotes, setQuotes] = useState<StoredQuote[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState<string | null>(null);
  const [openKey, setOpenKey] = useState<string | null>(null);

  useEffect(() => {
    let active = true;

    fetchQuotes()
      .then((data) => {
        if (active) {
          setQuotes(data);
        }
      })
      .catch((err: { message?: string }) => {
        if (active) {
          setError(err.message ?? "Não foi possível carregar as cotações.");
        }
      })
      .finally(() => {
        if (active) {
          setLoading(false);
        }
      });

    return () => {
      active = false;
    };
  }, []);

  function toggleQuote(key: string) {
    setOpenKey((current) => (current === key ? null : key));
  }

  return (
    <div className="min-h-full bg-zinc-50 px-4 py-10">
      <main className="mx-auto flex w-full max-w-3xl flex-col gap-8">
        <header className="space-y-2">
          <div className="flex flex-wrap items-center justify-between gap-3">
            <h1 className="text-3xl font-semibold tracking-tight text-zinc-900">
              Cotações salvas
            </h1>
            <Link
              href="/"
              className="rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
            >
              Nova cotação
            </Link>
          </div>
          <p className="text-sm text-zinc-600">
            Clique em uma cotação para ver os detalhes completos.
          </p>
        </header>

        {loading && (
          <section className="rounded-2xl border border-zinc-200 bg-white p-6 text-sm text-zinc-600 shadow-sm">
            Carregando cotações...
          </section>
        )}

        {error && (
          <section className="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-800 shadow-sm">
            <h2 className="mb-2 font-medium">Erro</h2>
            <p className="text-sm">{error}</p>
          </section>
        )}

        {!loading && !error && quotes.length === 0 && (
          <section className="rounded-2xl border border-zinc-200 bg-white p-6 text-sm text-zinc-600 shadow-sm">
            Nenhuma cotação encontrada.
          </section>
        )}

        {!loading && !error && quotes.length > 0 && (
          <section className="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
            {quotes.map((quote, index) => {
              const key = quoteKey(quote, index);
              const isOpen = openKey === key;

              return (
                <article
                  key={key}
                  className="border-b border-zinc-200 last:border-b-0"
                >
                  <button
                    type="button"
                    onClick={() => toggleQuote(key)}
                    aria-expanded={isOpen}
                    className="flex w-full items-center justify-between gap-4 px-5 py-4 text-left transition hover:bg-zinc-50"
                  >
                    <div>
                      <p className="text-sm text-zinc-500">Data início</p>
                      <p className="text-base font-medium text-zinc-900">
                        {formatDate(quote.data_inicio)}
                      </p>
                    </div>

                    <div className="flex items-center gap-4">
                      <div className="text-right">
                        <p className="text-sm text-zinc-500">Total</p>
                        <p className="text-base font-semibold text-emerald-700">
                          {formatBRL(quote.total_final)}
                        </p>
                      </div>
                      <span
                        className={`text-zinc-400 transition-transform ${isOpen ? "rotate-180" : ""}`}
                        aria-hidden
                      >
                        ▼
                      </span>
                    </div>
                  </button>

                  <div
                    className={`grid transition-all duration-300 ease-in-out ${
                      isOpen ? "grid-rows-[1fr] opacity-100" : "grid-rows-[0fr] opacity-0"
                    }`}
                  >
                    <div className="overflow-hidden">
                      {isOpen && <QuoteDetail quote={quote} />}
                    </div>
                  </div>
                </article>
              );
            })}
          </section>
        )}
      </main>
    </div>
  );
}
