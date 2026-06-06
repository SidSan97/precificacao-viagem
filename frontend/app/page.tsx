"use client";

import { FormEvent, useState } from "react";

import { formatValidationError } from "./hookies/format-errors";
import type { Adicional, Destino } from "./hookies/types";
import { useQuote } from "./hookies/useQuote";

const DESTINOS = [
  { value: "NACIONAL", label: "Nacional" },
  { value: "AMERICAS", label: "Américas" },
  { value: "EUROPA", label: "Europa" },
] as const;

const ADICIONAIS = [
  { value: "BAGAGEM", label: "Bagagem" },
  { value: "ESPORTES_AVENTURA", label: "Esportes de aventura" },
] as const;

interface ViajanteForm {
  nome: string;
  data_nascimento: string;
  adicionais: Adicional[];
}

function createViajante(): ViajanteForm {
  return { nome: "", data_nascimento: "", adicionais: [] };
}

function formatBRL(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value);
}

function adicionalLabel(value: Adicional): string {
  return ADICIONAIS.find((item) => item.value === value)?.label ?? value;
}

export default function Home() {
  const [destino, setDestino] = useState<Destino>("NACIONAL");
  const [dataInicio, setDataInicio] = useState("");
  const [dataFim, setDataFim] = useState("");
  const [viajantes, setViajantes] = useState<ViajanteForm[]>([createViajante()]);
  const { loading, result, errors, generalError, submitQuote } = useQuote();

  function updateViajante(
    index: number,
    field: keyof Omit<ViajanteForm, "adicionais">,
    value: string
  ) {
    setViajantes((current) =>
      current.map((viajante, i) =>
        i === index ? { ...viajante, [field]: value } : viajante
      )
    );
  }

  function toggleAdicional(index: number, adicional: Adicional) {
    setViajantes((current) =>
      current.map((viajante, i) => {
        if (i !== index) return viajante;

        const selected = viajante.adicionais.includes(adicional);

        return {
          ...viajante,
          adicionais: selected
            ? viajante.adicionais.filter((item) => item !== adicional)
            : [...viajante.adicionais, adicional],
        };
      })
    );
  }

  function addViajante() {
    setViajantes((current) => [...current, createViajante()]);
  }

  function removeViajante(index: number) {
    setViajantes((current) =>
      current.length === 1 ? current : current.filter((_, i) => i !== index)
    );
  }

  function handleSubmit(event: FormEvent<HTMLFormElement>) {
    event.preventDefault();

    submitQuote({
      destino,
      data_inicio: dataInicio,
      data_fim: dataFim,
      viajantes: viajantes.map((viajante) => ({
        nome: viajante.nome,
        data_nascimento: viajante.data_nascimento,
        adicionais: viajante.adicionais,
      })),
    });
  }

  const fieldClass =
    "w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm text-zinc-900 outline-none transition focus:border-zinc-500 focus:ring-2 focus:ring-zinc-200";

  return (
    <div className="min-h-full bg-zinc-50 px-4 py-10">
      <main className="mx-auto flex w-full max-w-3xl flex-col gap-8">
        <header className="space-y-2">
          <h1 className="text-3xl font-semibold tracking-tight text-zinc-900">
            Cotação de viagem
          </h1>
          <p className="text-sm text-zinc-600">
            Preencha os dados da viagem e dos viajantes para calcular o valor.
          </p>
        </header>

        <form onSubmit={handleSubmit} className="space-y-6">
          <section className="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm">
            <h2 className="mb-4 text-lg font-medium text-zinc-900">Viagem</h2>

            <div className="grid gap-4 sm:grid-cols-2">
              <label className="flex flex-col gap-1.5 sm:col-span-2">
                <span className="text-sm font-medium text-zinc-700">Destino</span>
                <select
                  className={fieldClass}
                  value={destino}
                  onChange={(event) => setDestino(event.target.value as Destino)}
                  required
                >
                  {DESTINOS.map((item) => (
                    <option key={item.value} value={item.value}>
                      {item.label}
                    </option>
                  ))}
                </select>
              </label>

              <label className="flex flex-col gap-1.5">
                <span className="text-sm font-medium text-zinc-700">Data início</span>
                <input
                  type="date"
                  className={fieldClass}
                  value={dataInicio}
                  onChange={(event) => setDataInicio(event.target.value)}
                  required
                />
              </label>

              <label className="flex flex-col gap-1.5">
                <span className="text-sm font-medium text-zinc-700">Data fim</span>
                <input
                  type="date"
                  className={fieldClass}
                  value={dataFim}
                  min={dataInicio || undefined}
                  onChange={(event) => setDataFim(event.target.value)}
                  required
                />
              </label>
            </div>
          </section>

          <section className="space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="text-lg font-medium text-zinc-900">Viajantes</h2>
              <button
                type="button"
                onClick={addViajante}
                className="rounded-lg border border-zinc-300 px-3 py-1.5 text-sm font-medium text-zinc-700 transition hover:bg-zinc-100"
              >
                Adicionar viajante
              </button>
            </div>

            {viajantes.map((viajante, index) => (
              <article
                key={index}
                className="rounded-2xl border border-zinc-200 bg-white p-6 shadow-sm"
              >
                <div className="mb-4 flex items-center justify-between">
                  <h3 className="font-medium text-zinc-900">Viajante {index + 1}</h3>
                  {viajantes.length > 1 && (
                    <button
                      type="button"
                      onClick={() => removeViajante(index)}
                      className="text-sm text-red-600 transition hover:text-red-700"
                    >
                      Remover
                    </button>
                  )}
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                  <label className="flex flex-col gap-1.5 sm:col-span-2">
                    <span className="text-sm font-medium text-zinc-700">Nome</span>
                    <input
                      type="text"
                      className={fieldClass}
                      value={viajante.nome}
                      onChange={(event) =>
                        updateViajante(index, "nome", event.target.value)
                      }
                      required
                      maxLength={255}
                    />
                  </label>

                  <label className="flex flex-col gap-1.5">
                    <span className="text-sm font-medium text-zinc-700">
                      Data de nascimento
                    </span>
                    <input
                      type="date"
                      className={fieldClass}
                      value={viajante.data_nascimento}
                      max={new Date().toISOString().split("T")[0]}
                      onChange={(event) =>
                        updateViajante(index, "data_nascimento", event.target.value)
                      }
                      required
                    />
                  </label>

                  <fieldset className="flex flex-col gap-2 sm:col-span-2">
                    <legend className="text-sm font-medium text-zinc-700">
                      Adicionais
                    </legend>
                    <div className="flex flex-wrap gap-4">
                      {ADICIONAIS.map((adicional) => (
                        <label
                          key={adicional.value}
                          className="flex items-center gap-2 text-sm text-zinc-700"
                        >
                          <input
                            type="checkbox"
                            className="h-4 w-4 rounded border-zinc-300"
                            checked={viajante.adicionais.includes(adicional.value)}
                            onChange={() =>
                              toggleAdicional(index, adicional.value)
                            }
                          />
                          {adicional.label}
                        </label>
                      ))}
                    </div>
                  </fieldset>
                </div>
              </article>
            ))}
          </section>

          <button
            type="submit"
            disabled={loading}
            className="w-full rounded-xl bg-zinc-900 px-4 py-3 text-sm font-medium text-white transition hover:bg-zinc-800 disabled:cursor-not-allowed disabled:opacity-60"
          >
            {loading ? "Calculando..." : "Calcular cotação"}
          </button>
        </form>

        {generalError && (
          <section className="rounded-2xl border border-red-200 bg-red-50 p-6 text-red-800">
            <h2 className="mb-2 font-medium">Erro</h2>
            <p className="text-sm">{generalError}</p>
            {Object.keys(errors).length > 0 && (
              <ul className="mt-3 list-disc space-y-1 pl-5 text-sm">
                {Object.entries(errors).flatMap(([field, messages]) =>
                  messages.map((message) => (
                    <li key={`${field}-${message}`}>
                      {formatValidationError(field, message)}
                    </li>
                  ))
                )}
              </ul>
            )}
          </section>
        )}

        {result && (
          <section className="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm">
            <h2 className="mb-4 text-lg font-medium text-zinc-900">Resultado</h2>

            <div className="mb-6 grid gap-3 rounded-xl bg-zinc-50 p-4 text-sm sm:grid-cols-3">
              <div>
                <p className="text-zinc-500">Dias cobrados</p>
                <p className="font-medium text-zinc-900">{result.dias_cobrados}</p>
              </div>
              <div>
                <p className="text-zinc-500">Desconto de grupo</p>
                <p className="font-medium text-zinc-900">
                  {result.desconto_grupo_percentual}%
                </p>
              </div>
              <div>
                <p className="text-zinc-500">Total final</p>
                <p className="text-lg font-semibold text-emerald-700">
                  {formatBRL(result.total_final)}
                </p>
              </div>
            </div>

            <div className="space-y-3">
              {result.viajantes.map((viajante) => (
                <article
                  key={viajante.nome}
                  className="rounded-xl border border-zinc-200 p-4"
                >
                  <div className="flex flex-wrap items-start justify-between gap-2">
                    <div>
                      <p className="font-medium text-zinc-900">{viajante.nome}</p>
                      <p className="text-sm text-zinc-500">
                        {viajante.idade} anos
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
                        .map(adicionalLabel)
                        .join(", ")}
                    </p>
                  )}
                </article>
              ))}
            </div>

            {result.avisos.length > 0 && (
              <div className="mt-4 rounded-xl border border-amber-200 bg-amber-50 p-4">
                <h3 className="mb-2 text-sm font-medium text-amber-900">Avisos</h3>
                <ul className="list-disc space-y-1 pl-5 text-sm text-amber-800">
                  {result.avisos.map((aviso) => (
                    <li key={aviso}>{aviso}</li>
                  ))}
                </ul>
              </div>
            )}
          </section>
        )}
      </main>
    </div>
  );
}
