<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Str;

class QuoteGroup extends Model
{
    protected $table = 'quote_group';

    /**
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'dias_cobrados',
        'total_final',
    ];

    protected static function booted(): void
    {
        static::creating(function (QuoteGroup $quoteGroup): void {
            if (empty($quoteGroup->uuid)) {
                $quoteGroup->uuid = (string) Str::uuid();
            }
        });
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'dias_cobrados' => 'integer',
            'total_final' => 'decimal:2',
        ];
    }

    public function viajantes(): HasMany
    {
        return $this->hasMany(Viajante::class);
    }

    public function avisos(): HasManyThrough
    {
        return $this->hasManyThrough(Aviso::class, Viajante::class);
    }
}
