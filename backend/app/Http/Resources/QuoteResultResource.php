<?php

namespace App\Http\Resources;

use App\Support\MoneyFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteResultResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'dias_cobrados' => $this->resource['dias_cobrados'],
            'viajantes' => collect($this->resource['viajantes'])
                ->map(fn (array $viajante): array => [
                    'nome' => $viajante['nome'],
                    'idade' => $viajante['idade'],
                    'subtotal' => MoneyFormatter::format($viajante['subtotal']),
                    'adicionais_aplicados' => $viajante['adicionais_aplicados'],
                ])
                ->all(),
            'avisos' => $this->resource['avisos'],
            'desconto_grupo_percentual' => $this->resource['desconto_grupo_percentual'],
            'total_final' => MoneyFormatter::format($this->resource['total_final']),
        ];
    }
}
