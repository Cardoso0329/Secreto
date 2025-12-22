<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vista extends Model
{
    protected $fillable = [
        'nome',
        'user_id',
        'acesso',
        'filtros',
    ];

    protected $casts = [
        'filtros' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
