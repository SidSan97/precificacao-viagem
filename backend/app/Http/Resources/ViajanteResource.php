<?php

namespace App\Http\Resources;

use App\Support\MoneyFormatter;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ViajanteResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'nome' => $this->nome,
            'data_nascimento' => $this->data_nascimento?->format('Y-m-d'),
            'subtotal' => MoneyFormatter::format($this->subtotal),
            'adicionais_aplicados' => $this->adicionais_aplicados,
            'avisos' => AvisoResource::collection($this->whenLoaded('avisos')),
        ];
    }
}
