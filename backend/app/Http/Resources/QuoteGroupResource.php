<?php

namespace App\Http\Resources;

use App\Support\MoneyFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuoteGroupResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'dias_cobrados' => $this->dias_cobrados,
            'total_final' => MoneyFormatter::format($this->total_final),
            'data_inicio' => $this->data_inicio,
            'data_fim' => $this->data_fim,
            'destino' => $this->destino,
            'viajantes' => ViajanteResource::collection($this->whenLoaded('viajantes')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
