function getFieldLabel(field: string): string | null {
  if (field === "data_inicio") return "Data início";
  if (field === "data_fim") return "Data fim";

  const birthDateMatch = field.match(/^viajantes\.(\d+)\.data_nascimento$/);
  if (birthDateMatch) {
    return `Data de nascimento (viajante ${Number(birthDateMatch[1]) + 1})`;
  }

  return null;
}

function normalizeMessage(message: string): string {
  if (/must match the format Y-m-d/i.test(message)) {
    return "Informe uma data válida (ex.: 2026-07-10).";
  }

  if (/is not a valid date/i.test(message)) {
    return "Informe uma data válida.";
  }

  return message;
}

export function formatValidationError(field: string, message: string): string {
  const label = getFieldLabel(field);
  const text = normalizeMessage(message);

  if (!label) {
    return text;
  }

  if (text.startsWith(label) || text.toLowerCase().includes(label.toLowerCase())) {
    return text;
  }

  return `${label}: ${text}`;
}
