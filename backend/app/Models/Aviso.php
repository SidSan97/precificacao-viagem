<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Aviso extends Model
{
    /**
     * @var list<string>
     */
    protected $fillable = [
        'viajante_id',
        'aviso',
    ];

    public function viajante(): BelongsTo
    {
        return $this->belongsTo(Viajante::class);
    }
}
