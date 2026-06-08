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

    public function test_allows_multiple_travelers_without_addons(): void
    {
        $response = $this->postJson('/api/quotes', [
            'destino' => 'NACIONAL',
            'data_inicio' => '2026-07-10',
            'data_fim' => '2026-07-20',
            'viajantes' => [
                [
                    'nome' => 'Ana',
                    'data_nascimento' => '1990-03-15',
                    'adicionais' => [],
                ],
                [
                    'nome' => 'João',
                    'data_nascimento' => '1985-11-02',
                    'adicionais' => [],
                ],
            ],
        ]);

        $response->assertJsonMissingValidationErrors();
        $this->assertNotSame(422, $response->getStatusCode());
    }

    public function test_rejects_duplicate_addons_within_same_traveler(): void
    {
        $response = $this->postJson('/api/quotes', [
            'destino' => 'NACIONAL',
            'data_inicio' => '2026-07-10',
            'data_fim' => '2026-07-20',
            'viajantes' => [
                [
                    'nome' => 'Ana',
                    'data_nascimento' => '1990-03-15',
                    'adicionais' => ['BAGAGEM', 'BAGAGEM'],
                ],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['viajantes.0.adicionais']);
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
