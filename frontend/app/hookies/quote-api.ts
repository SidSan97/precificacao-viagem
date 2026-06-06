import type { QuoteApiError, QuotePayload, QuoteResult } from "./types";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

export async function calculateQuote(payload: QuotePayload): Promise<QuoteResult> {
  const response = await fetch(`${API_URL}/quotes`, {
    method: "POST",
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
    },
    body: JSON.stringify(payload),
  });

  const json = await response.json();

  if (!response.ok) {
    const error: QuoteApiError = {
      message: json.message ?? "Não foi possível calcular a cotação.",
      errors: json.errors ?? {},
    };

    throw error;
  }

  return (json.data ?? json) as QuoteResult;
}
