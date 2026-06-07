<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Viajante extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'quote_group_id',
        'nome',
        'data_nascimento',
        'subtotal',
        'adicionais_aplicados',
    ];

    protected static function booted(): void
    {
        static::creating(function (Viajante $viajante): void {
            if (empty($viajante->uuid)) {
                $viajante->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_nascimento' => 'date',
            'subtotal' => 'decimal:2',
            'adicionais_aplicados' => 'array',
        ];
    }

    public function quoteGroup(): BelongsTo
    {
        return $this->belongsTo(QuoteGroup::class);
    }

    public function avisos(): HasMany
    {
        return $this->hasMany(Aviso::class);
    }
}
