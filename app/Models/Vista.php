<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Queries\RecadoQuery;

class Vista extends Model
{
    protected $fillable = [
        'nome',
        'filtros',
        'logica',
        'acesso',
        'user_id'
    ];

    protected $casts = [
        'filtros' => 'array' // já existente
    ];

    /* ================= RELAÇÕES ================= */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /* ================= MÉTODO APPLY ================= */
    public function apply($query)
    {
        // Garante que $filtros é array
        $filtros = $this->filtros;

        if (is_string($filtros)) {
            $filtros = json_decode($filtros, true) ?? [];
        }

        // Garante lógica padrão
        $logica = $this->logica ?? 'AND';

        return RecadoQuery::applyFilters($query, $filtros, $logica);
    }

    /* ================= VISIBILIDADE ================= */
    public static function visiveisPara($user)
    {
        return self::query()->where(function ($q) use ($user) {
            $q->where('acesso', 'all')
              ->orWhere(function ($q2) use ($user) {
                  $q2->where('acesso', 'owner')
                     ->where('user_id', $user->id);
              });
        });
    }
}
