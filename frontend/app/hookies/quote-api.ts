import type {
  QuoteApiError,
  QuotePayload,
  QuoteResult,
  StoredQuote,
} from "./types";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

export async function fetchQuotes(): Promise<StoredQuote[]> {
  const response = await fetch(`${API_URL}/quotes`, {
    headers: {
      Accept: "application/json",
    },
    cache: "no-store",
  });

  const json = await response.json();

  if (!response.ok) {
    const error: QuoteApiError = {
      message: json.message ?? "Não foi possível carregar as cotações.",
      errors: json.errors ?? {},
    };

    throw error;
  }

  return (json.data ?? json) as StoredQuote[];
}

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
