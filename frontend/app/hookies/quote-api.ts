import { parseApiError } from "./parse-api-error";
import type { QuotePayload, QuoteResult, StoredQuote } from "./types";
import { QUOTE_SERVER_ERROR_MESSAGE } from "./types";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api";

export async function fetchQuotes(): Promise<StoredQuote[]> {
  const response = await fetch(`${API_URL}/quotes`, {
    headers: {
      Accept: "application/json",
    },
    cache: "no-store",
  });

  if (!response.ok) {
    throw await parseApiError(
      response,
      "Não foi possível carregar as cotações.",
      "Não foi possível carregar as cotações. Tente novamente mais tarde."
    );
  }

  const json = await response.json();

  return (json.data ?? json) as StoredQuote[];
}

export async function calculateQuote(payload: QuotePayload): Promise<QuoteResult> {
  let response: Response;

  try {
    response = await fetch(`${API_URL}/quotes`, {
      method: "POST",
      headers: {
        Accept: "application/json",
        "Content-Type": "application/json",
      },
      body: JSON.stringify(payload),
    });
  } catch {
    throw {
      message: QUOTE_SERVER_ERROR_MESSAGE,
      errors: {},
    };
  }

  if (!response.ok) {
    throw await parseApiError(
      response,
      "Não foi possível calcular a cotação.",
      QUOTE_SERVER_ERROR_MESSAGE
    );
  }

  const json = await response.json();

  return (json.data ?? json) as QuoteResult;
}
