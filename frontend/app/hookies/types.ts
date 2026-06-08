export type Destino = "NACIONAL" | "AMERICAS" | "EUROPA";

export type Adicional = "BAGAGEM" | "ESPORTES_AVENTURA";

export interface QuotePayload {
  destino: Destino;
  data_inicio: string;
  data_fim: string;
  viajantes: Array<{
    nome: string;
    data_nascimento: string;
    adicionais: Adicional[];
  }>;
}

export interface QuoteResult {
  dias_cobrados: number;
  viajantes: Array<{
    nome: string;
    idade: number;
    subtotal: number;
    adicionais_aplicados: Adicional[];
  }>;
  avisos: string[];
  desconto_grupo_percentual: number;
  total_final: number;
}

export interface QuoteApiError {
  message: string;
  errors: Record<string, string[]>;
  status?: number;
}

export const QUOTE_SERVER_ERROR_MESSAGE =
  "Não foi possível calcular o preço. Tente novamente mais tarde.";

export interface StoredQuoteAviso {
  aviso: string;
}

export interface StoredQuoteViajante {
  nome: string;
  data_nascimento: string;
  subtotal: string | number;
  adicionais_aplicados: Adicional[];
  avisos: StoredQuoteAviso[];
}

export interface StoredQuote {
  dias_cobrados: number;
  total_final: string | number;
  data_inicio: string;
  data_fim: string;
  destino: Destino;
  viajantes: StoredQuoteViajante[];
  created_at: string;
  updated_at: string;
}
