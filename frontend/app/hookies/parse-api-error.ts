import type { QuoteApiError } from "./types";
import { QUOTE_SERVER_ERROR_MESSAGE } from "./types";

export async function parseApiError(
  response: Response,
  fallbackMessage: string,
  serverErrorMessage: string = QUOTE_SERVER_ERROR_MESSAGE
): Promise<QuoteApiError> {
  if (response.status >= 500) {
    return {
      message: serverErrorMessage,
      errors: {},
      status: response.status,
    };
  }

  let json: Record<string, unknown> = {};

  try {
    const parsed: unknown = await response.json();

    if (parsed && typeof parsed === "object" && !Array.isArray(parsed)) {
      json = parsed as Record<string, unknown>;
    }
  } catch {
    return {
      message: fallbackMessage,
      errors: {},
      status: response.status,
    };
  }

  const rawErrors = json.errors;

  return {
    message:
      typeof json.message === "string" ? json.message : fallbackMessage,
    errors:
      rawErrors &&
      typeof rawErrors === "object" &&
      !Array.isArray(rawErrors)
        ? (rawErrors as Record<string, string[]>)
        : {},
    status: response.status,
  };
}
