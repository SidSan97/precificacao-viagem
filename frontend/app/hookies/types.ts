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
}
