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
        
    }

    private function calculateChargedDays(string $dataInicio, string $dataFim): int
    {
        $inicio = Carbon::parse($dataInicio)->startOfDay();
        $fim = Carbon::parse($dataFim)->startOfDay();

        $dias = $inicio->diffInDays($fim) + 1;

        return (int) max($dias, self::MIN_CHARGED_DAYS);
    }
}
