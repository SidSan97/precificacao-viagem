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

        $this->assertSame(11, $result['dias_cobrados']);
        $this->assertSame(36, $result['viajantes'][0]['idade']);
        $this->assertSame(335.5, $result['viajantes'][0]['subtotal']);
        $this->assertSame(['BAGAGEM', 'ESPORTES_AVENTURA'], $result['viajantes'][0]['adicionais_aplicados']);
        $this->assertSame(77, $result['viajantes'][1]['idade']);
        $this->assertSame(517.0, $result['viajantes'][1]['subtotal']);
        $this->assertSame(['BAGAGEM'], $result['viajantes'][1]['adicionais_aplicados']);
        $this->assertSame([
            'ESPORTES_AVENTURA não aplicado para João: fora da faixa etária permitida (18-64).',
        ], $result['avisos']);
        $this->assertSame(0, $result['desconto_grupo_percentual']);
        $this->assertSame(852.5, $result['total_final']);
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

        $this->assertSame(5, $result['dias_cobrados']);
        $this->assertSame(50.0, $result['viajantes'][0]['subtotal']);
        $this->assertSame(50.0, $result['total_final']);
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

        $this->assertSame(10, $result['desconto_grupo_percentual']);
        $this->assertSame(225.0, $result['total_final']);
    }
}
