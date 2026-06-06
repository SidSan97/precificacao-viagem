import { forwardRef } from "react";

import type { Adicional, QuoteResult } from "../hookies/types";

const ADICIONAL_LABELS: Record<Adicional, string> = {
  BAGAGEM: "Bagagem",
  ESPORTES_AVENTURA: "Esportes de aventura",
};

interface CardResultProps {
  result: QuoteResult;
}

function formatBRL(value: number): string {
  return new Intl.NumberFormat("pt-BR", {
    style: "currency",
    currency: "BRL",
  }).format(value);
}

function adicionalLabel(value: Adicional): string {
  return ADICIONAL_LABELS[value];
}

const CardResult = forwardRef<HTMLElement, CardResultProps>(function CardResult(
  { result },
  ref
) {
  return (
    <section
      ref={ref}
      className="rounded-2xl border border-emerald-200 bg-white p-6 shadow-sm"
    >
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
                <p className="text-sm text-zinc-500">{viajante.idade} anos</p>
              </div>
              <p className="font-medium text-zinc-900">
                {formatBRL(viajante.subtotal)}
              </p>
            </div>
            {viajante.adicionais_aplicados.length > 0 && (
              <p className="mt-2 text-sm text-zinc-600">
                Adicionais:{" "}
                {viajante.adicionais_aplicados.map(adicionalLabel).join(", ")}
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
  );
});

export default CardResult;
