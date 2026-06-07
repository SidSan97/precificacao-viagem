<?php

namespace App\Repositories;

use App\Models\Aviso;
use App\Models\QuoteGroup;
use App\Models\Viajante;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class QuoteRepository
{
    public function all(): Collection
    {
        return QuoteGroup::query()
            ->select(['id', 'dias_cobrados', 'total_final', 'created_at', 'updated_at'])
            ->with([
                'viajantes:id,quote_group_id,nome,data_nascimento,subtotal,adicionais_aplicados',
                'viajantes.avisos:id,viajante_id,aviso',
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    public function create(array $payload, array $result): QuoteGroup
    {
        return DB::transaction(function () use ($payload, $result): QuoteGroup {
            $quoteGroup = QuoteGroup::create([
                'dias_cobrados' => $result['dias_cobrados'],
                'total_final' => $result['total_final'],
            ]);

            $viajantes = $this->criarViajantes($quoteGroup, $payload['viajantes'], $result['viajantes']);

            $this->criarAvisos($viajantes, $result['avisos']);

            return $quoteGroup->load(['viajantes.avisos']);
        });
    }

    private function criarViajantes(QuoteGroup $quoteGroup, array $payloadViajantes, array $resultViajantes): Collection
    {
        $viajantes = collect();

        foreach ($resultViajantes as $index => $resultViajante) {
            $payloadViajante = $payloadViajantes[$index];

            $viajantes->push(
                $quoteGroup->viajantes()->create([
                    'nome' => $resultViajante['nome'],
                    'data_nascimento' => $payloadViajante['data_nascimento'],
                    'subtotal' => $resultViajante['subtotal'],
                    'adicionais_aplicados' => $resultViajante['adicionais_aplicados'],
                ])
            );
        }

        return $viajantes;
    }

    private function criarAvisos(Collection $viajantes, array $avisos): void
    {
        foreach ($avisos as $mensagem) {
            $viajante = $this->normalizarAvisosPorViajantes($viajantes, $mensagem);

            if ($viajante === null) {
                continue;
            }

            Aviso::create([
                'viajante_id' => $viajante->id,
                'aviso' => $mensagem,
            ]);
        }
    }

    private function normalizarAvisosPorViajantes(Collection $viajantes, string $mensagem): ?Viajante
    {
        return $viajantes->first(
            fn (Viajante $viajante): bool => str_contains($mensagem, $viajante->nome)
        );
    }
}
