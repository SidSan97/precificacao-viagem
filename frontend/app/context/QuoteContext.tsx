"use client";

import {
  createContext,
  useCallback,
  useContext,
  useMemo,
  useState,
  type ReactNode,
} from "react";

import { calculateQuote } from "../hookies/quote-api";
import type { QuotePayload, QuoteResult } from "../hookies/types";
import { QUOTE_SERVER_ERROR_MESSAGE } from "../hookies/types";

interface QuoteContextValue {
  loading: boolean;
  result: QuoteResult | null;
  errors: Record<string, string[]>;
  generalError: string | null;
  submitQuote: (payload: QuotePayload) => Promise<boolean>;
  clearQuote: () => void;
}

const QuoteContext = createContext<QuoteContextValue | null>(null);

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

export function QuoteProvider({ children }: { children: ReactNode }) {
  const [loading, setLoading] = useState(false);
  const [result, setResult] = useState<QuoteResult | null>(null);
  const [errors, setErrors] = useState<Record<string, string[]>>({});
  const [generalError, setGeneralError] = useState<string | null>(null);

  const clearQuote = useCallback(() => {
    setResult(null);
    setErrors({});
    setGeneralError(null);
  }, []);

  const submitQuote = useCallback(async (payload: QuotePayload): Promise<boolean> => {
    setLoading(true);
    setResult(null);
    setErrors({});
    setGeneralError(null);

    try {
      const quote = await calculateQuote(payload);
      setResult(quote);
      return true;
    } catch (error) {
      if (isQuoteApiError(error)) {
        setErrors(error.errors ?? {});
        setGeneralError(error.message);
        return false;
      }

      setGeneralError(QUOTE_SERVER_ERROR_MESSAGE);
      return false;
    } finally {
      setLoading(false);
    }
  }, []);

  const value = useMemo(
    () => ({
      loading,
      result,
      errors,
      generalError,
      submitQuote,
      clearQuote,
    }),
    [loading, result, errors, generalError, submitQuote, clearQuote]
  );

  return <QuoteContext.Provider value={value}>{children}</QuoteContext.Provider>;
}

export function useQuoteContext(): QuoteContextValue {
  const context = useContext(QuoteContext);

  if (!context) {
    throw new Error("useQuoteContext deve ser usado dentro de QuoteProvider.");
  }

  return context;
}
