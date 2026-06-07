<?php

namespace Tests\Unit;

use App\Services\QuoteCalculatorService;
use PHPUnit\Framework\TestCase;

class QuoteCalculatorServiceTest extends TestCase
{
    // Cenário completo: 2 viajantes, add-ons, idade na data de início e aviso de esportes negado.
    public function test_calculates_quote_from_business_rules_example(): void
    {
        $service = new QuoteCalculatorService();

        $result = $service->calculate([
            'destino' => 'EUROPA',
            'data_inicio' => '2026-07-10',
            'data_fim' => '2026-07-20',
            'viajantes' => [
                [
                    'nome' => 'Ana',
                    'data_nascimento' => '1990-03-15',
                    'adicionais' => ['BAGAGEM', 'ESPORTES_AVENTURA'],
                ],
                [
                    'nome' => 'João',
                    'data_nascimento' => '1948-11-02',
                    'adicionais' => ['ESPORTES_AVENTURA', 'BAGAGEM'],
                ],
            ],
        ]);

        $this->assertSame([
            'dias_cobrados' => 11,
            'viajantes' => [
                [
                    'nome' => 'Ana',
                    'idade' => 36,
                    'subtotal' => 335.5,
                    'adicionais_aplicados' => ['BAGAGEM', 'ESPORTES_AVENTURA'],
                ],
                [
                    'nome' => 'João',
                    'idade' => 77,
                    'subtotal' => 517.0,
                    'adicionais_aplicados' => ['BAGAGEM'],
                ],
            ],
            'avisos' => [
                'ESPORTES_AVENTURA não aplicado para João: fora da faixa etária permitida (18-64).',
            ],
            'desconto_grupo_percentual' => 0,
            'total_final' => 852.5,
        ], $result);
    }

    // Menor de idade não recebe o acréscimo de ESPORTES_AVENTURA, apenas o aviso.
    public function test_does_not_apply_esportes_aventura_for_minor(): void
    {
        $service = new QuoteCalculatorService();

        $result = $service->calculate([
            'destino' => 'NACIONAL',
            'data_inicio' => '2026-07-10',
            'data_fim' => '2026-07-20',
            'viajantes' => [
                [
                    'nome' => 'Pedro',
                    'data_nascimento' => '2012-05-10',
                    'adicionais' => ['ESPORTES_AVENTURA'],
                ],
            ],
        ]);

        $this->assertSame([
            'dias_cobrados' => 11,
            'viajantes' => [
                [
                    'nome' => 'Pedro',
                    'idade' => 14,
                    'subtotal' => 55.0,
                    'adicionais_aplicados' => [],
                ],
            ],
            'avisos' => [
                'ESPORTES_AVENTURA não aplicado para Pedro: fora da faixa etária permitida (18-64).',
            ],
            'desconto_grupo_percentual' => 0,
            'total_final' => 55.0,
        ], $result);
    }

    // Idade é calculada na data de início da viagem, não na data da cotação.
    public function test_uses_age_at_trip_start_not_quote_date(): void
    {
        $service = new QuoteCalculatorService();

        $viajante = [
            'nome' => 'Carlos',
            'data_nascimento' => '1961-08-15',
            'adicionais' => [],
        ];

        $resultAfterBirthday = $service->calculate([
            'destino' => 'NACIONAL',
            'data_inicio' => '2026-09-01',
            'data_fim' => '2026-09-01',
            'viajantes' => [$viajante],
        ]);

        $resultBeforeBirthday = $service->calculate([
            'destino' => 'NACIONAL',
            'data_inicio' => '2026-08-01',
            'data_fim' => '2026-08-01',
            'viajantes' => [$viajante],
        ]);

        $this->assertSame([
            'dias_cobrados' => 5,
            'viajantes' => [
                [
                    'nome' => 'Carlos',
                    'idade' => 65,
                    'subtotal' => 100.0,
                    'adicionais_aplicados' => [],
                ],
            ],
            'avisos' => [],
            'desconto_grupo_percentual' => 0,
            'total_final' => 100.0,
        ], $resultAfterBirthday);

        $this->assertSame([
            'dias_cobrados' => 5,
            'viajantes' => [
                [
                    'nome' => 'Carlos',
                    'idade' => 64,
                    'subtotal' => 50.0,
                    'adicionais_aplicados' => [],
                ],
            ],
            'avisos' => [],
            'desconto_grupo_percentual' => 0,
            'total_final' => 50.0,
        ], $resultBeforeBirthday);
    }

    // Viagem com menos de 5 dias deve cobrar o período mínimo de 5 dias.
    public function test_applies_minimum_charged_days(): void
    {
        $service = new QuoteCalculatorService();

        $result = $service->calculate([
            'destino' => 'NACIONAL',
            'data_inicio' => '2026-07-10',
            'data_fim' => '2026-07-11',
            'viajantes' => [
                [
                    'nome' => 'Maria',
                    'data_nascimento' => '2000-01-01',
                    'adicionais' => [],
                ],
            ],
        ]);

        $this->assertSame([
            'dias_cobrados' => 5,
            'viajantes' => [
                [
                    'nome' => 'Maria',
                    'idade' => 26,
                    'subtotal' => 50.0,
                    'adicionais_aplicados' => [],
                ],
            ],
            'avisos' => [],
            'desconto_grupo_percentual' => 0,
            'total_final' => 50.0,
        ], $result);
    }

    // Grupos com 5 ou mais viajantes recebem 10% de desconto no total final.
    public function test_applies_group_discount_for_five_or_more_travelers(): void
    {
        $service = new QuoteCalculatorService();

        $viajantes = array_fill(0, 5, [
            'nome' => 'Viajante',
            'data_nascimento' => '1990-01-01',
            'adicionais' => [],
        ]);

        $result = $service->calculate([
            'destino' => 'NACIONAL',
            'data_inicio' => '2026-07-10',
            'data_fim' => '2026-07-10',
            'viajantes' => $viajantes,
        ]);

        $this->assertSame([
            'dias_cobrados' => 5,
            'viajantes' => array_fill(0, 5, [
                'nome' => 'Viajante',
                'idade' => 36,
                'subtotal' => 50.0,
                'adicionais_aplicados' => [],
            ]),
            'avisos' => [],
            'desconto_grupo_percentual' => 10,
            'total_final' => 225.0,
        ], $result);
    }
}
