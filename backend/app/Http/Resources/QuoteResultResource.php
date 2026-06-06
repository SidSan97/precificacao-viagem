<?php

namespace App\Http\Resources;

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
            'viajantes' => $this->resource['viajantes'],
            'avisos' => $this->resource['avisos'],
            'desconto_grupo_percentual' => $this->resource['desconto_grupo_percentual'],
            'total_final' => $this->resource['total_final'],
        ];
    }
}
