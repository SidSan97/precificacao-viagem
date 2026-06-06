"use client";

import { useState } from "react";

import { calculateQuote } from "./quote-api";
import type { QuotePayload, QuoteResult } from "./types";

export function useQuote() {
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<QuoteResult | null>(null);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [generalError, setGeneralError] = useState<string | null>(null);

  async function submitQuote(payload: QuotePayload) {
    setLoading(true);
    setResult(null);
    setErrors({});
    setGeneralError(null);

    try {
      const quote = await calculateQuote(payload);
      setResult(quote);
    } catch (error) {
      if (isQuoteApiError(error)) {
        setErrors(error.errors);
        setGeneralError(error.message);
        return;
      }

      setGeneralError(
        "Falha na comunicação com a API. Verifique se o backend está em execução."
      );
    } finally {
      setLoading(false);
    }
  }

  return {
    loading,
    result,
    errors,
    generalError,
    submitQuote,
  };
}

function isQuoteApiError(error: unknown): error is {
  message: string;
  errors: Record<string, string[]>;
} {
  return (
    typeof error === "object" &&
    error !== null &&
    "message" in error &&
    typeof error.message === "string"
  );
}
