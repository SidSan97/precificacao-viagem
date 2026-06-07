<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Tests\TestCase;

class CreateQuoteValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow('2026-06-01');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_rejects_unlisted_addon(): void
    {
        $response = $this->postJson('/api/quotes', [
            'destino' => 'NACIONAL',
            'data_inicio' => '2026-07-10',
            'data_fim' => '2026-07-20',
            'viajantes' => [
                [
                    'nome' => 'Ana',
                    'data_nascimento' => '1990-03-15',
                    'adicionais' => ['CANCELAMENTO'],
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['viajantes.0.adicionais.0']);
    }
}
