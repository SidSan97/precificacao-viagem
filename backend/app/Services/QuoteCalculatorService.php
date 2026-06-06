<?php

namespace App\Services;

use Carbon\Carbon;

class QuoteCalculatorService
{
    private const MIN_CHARGED_DAYS = 5;

    private const ZONE_DAILY_RATES = [
        'NACIONAL' => 10.0,
        'AMERICAS' => 16.0,
        'EUROPA' => 22.0,
    ];

    private const BAGAGEM_DAILY_RATE = 3.0;
    private const ESPORTES_AVENTURA_RATE = 0.25;
    private const ESPORTES_MIN_AGE = 18;
    private const ESPORTES_MAX_AGE = 64;
    private const GROUP_DISCOUNT_MIN_TRAVELERS = 5;
    private const GROUP_DISCOUNT_RATE = 0.10;

    public function calculate(array $data): array
    {
        $diasCobrados = $this->calculateChargedDays($data['data_inicio'], $data['data_fim']);
        $tarifaDiaria = $this->resolveDailyRate($data['destino']);
        $avisos = [];
        $viajantes = [];
        $subtotaisBrutos = [];

        foreach ($data['viajantes'] as $viajante) {
            $resultado = $this->calculateTraveler(
                $viajante,
                $tarifaDiaria,
                $diasCobrados,
                $data['data_inicio'],
                $avisos
            );

            $viajantes[] = $resultado['viajante'];
            $subtotaisBrutos[] = $resultado['subtotal_bruto'];
        }

        $descontoPercentual = $this->resolveGroupDiscountPercent(count($data['viajantes']));
        $totalFinal = $this->calculateFinalTotal($subtotaisBrutos, $descontoPercentual);

        return [
            'dias_cobrados' => $diasCobrados,
            'viajantes' => $viajantes,
            'avisos' => $avisos,
            'desconto_grupo_percentual' => $descontoPercentual,
            'total_final' => $totalFinal,
        ];
    }

    private function calculateChargedDays(string $dataInicio, string $dataFim): int
    {
        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim = Carbon::parse($dataFim)->startOfDay();

        $dias = $inicio->diffInDays($fim) + 1;

        return (int) max($dias, self::MIN_CHARGED_DAYS);
    }

    private function resolveDailyRate(string $destino): float
    {
        return self::ZONE_DAILY_RATES[$destino];
    }

    private function calculateTraveler(
        array $viajante,
        float $tarifaDiaria,
        int $diasCobrados,
        string $dataInicio,
        array &$avisos
    ): array {
        $idade = $this->calculateAgeAt($viajante['data_nascimento'], $dataInicio);
        $multiplicador = $this->resolveAgeMultiplier($idade);
        $adicionaisSolicitados = $viajante['adicionais'];

        $subtotal = $tarifaDiaria * $diasCobrados * $multiplicador;

        $subtotal = $this->applyEsportesAventura(
            $subtotal,
            $idade,
            $viajante['nome'],
            $adicionaisSolicitados,
            $avisos
        );

        $subtotal = $this->applyBagagem($diasCobrados, $adicionaisSolicitados, $subtotal);

        return [
            'viajante' => [
                'nome' => $viajante['nome'],
                'idade' => $idade,
                'subtotal' => $this->roundForDisplay($subtotal),
                'adicionais_aplicados' => $this->resolveAppliedAddons($adicionaisSolicitados, $idade),
            ],
            'subtotal_bruto' => $subtotal,
        ];
    }

    private function calculateAgeAt(string $dataNascimento, string $dataReferencia): int
    {
        return (int) Carbon::parse($dataNascimento)
            ->startOfDay()
            ->diff(Carbon::parse($dataReferencia)->startOfDay())
            ->y;
    }

    private function resolveAgeMultiplier(int $idade): float
    {
        if ($idade <= 17) {
            return 0.5;
        }

        if ($idade <= 64) {
            return 1.0;
        }

        return 2.0;
    }

    private function applyEsportesAventura(
        float $subtotal,
        int $idade,
        string $nome,
        array $adicionaisSolicitados,
        array &$avisos
    ): float {
        if (! in_array('ESPORTES_AVENTURA', $adicionaisSolicitados, true)) {
            return $subtotal;
        }

        if ($idade < self::ESPORTES_MIN_AGE || $idade > self::ESPORTES_MAX_AGE) {
            $avisos[] = sprintf(
                'ESPORTES_AVENTURA não aplicado para %s: fora da faixa etária permitida (%d-%d).',
                $nome,
                self::ESPORTES_MIN_AGE,
                self::ESPORTES_MAX_AGE
            );

            return $subtotal;
        }

        return $subtotal + ($subtotal * self::ESPORTES_AVENTURA_RATE);
    }

    private function applyBagagem(
        int $diasCobrados,
        array $adicionaisSolicitados,
        float $subtotal
    ): float {
        if (! in_array('BAGAGEM', $adicionaisSolicitados, true)) {
            return $subtotal;
        }

        return $subtotal + (self::BAGAGEM_DAILY_RATE * $diasCobrados);
    }

    private function resolveAppliedAddons(array $adicionaisSolicitados, int $idade): array
    {
        return array_values(array_filter(
            $adicionaisSolicitados,
            fn (string $adicional): bool => match ($adicional) {
                'BAGAGEM' => true,
                'ESPORTES_AVENTURA' => $idade >= self::ESPORTES_MIN_AGE && $idade <= self::ESPORTES_MAX_AGE,
                default => false,
            }
        ));
    }

    private function resolveGroupDiscountPercent(int $quantidadeViajantes): int
    {
        if ($quantidadeViajantes >= self::GROUP_DISCOUNT_MIN_TRAVELERS) {
            return (int) (self::GROUP_DISCOUNT_RATE * 100);
        }

        return 0;
    }

    private function calculateFinalTotal(array $subtotaisBrutos, int $descontoPercentual): float
    {
        $totalGrupo = array_sum($subtotaisBrutos);
        $desconto = $totalGrupo * ($descontoPercentual / 100);

        return $this->roundTotal($totalGrupo - $desconto);
    }

    private function roundForDisplay(float $value): float
    {
        return round($value, 2, PHP_ROUND_HALF_UP);
    }

    private function roundTotal(float $value): float
    {
        return round($value, 2, PHP_ROUND_HALF_UP);
    }
}
