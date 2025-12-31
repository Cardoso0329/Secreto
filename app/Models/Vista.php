<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

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
        'filtros' => 'array'
    ];

    /* ================= RELAÇÕES ================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function departamentos()
    {
        return $this->belongsToMany(Departamento::class, 'vista_departamento');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'vista_user');
    }

    /* ================= SCOPES ================= */

    public function scopeVisiveisPara($query, $user)
    {
        return $query->where(function ($q) use ($user) {
            $q->where('acesso', 'all')

              ->orWhere(function ($q) use ($user) {
                  $q->where('acesso', 'owner')
                    ->where('user_id', $user->id);
              })

              ->orWhere(function ($q) use ($user) {
                  $q->where('acesso', 'department')
                    ->whereHas('departamentos', function ($d) use ($user) {
                        $d->where('departamento_id', $user->departamento_id);
                    });
              })

              ->orWhere(function ($q) use ($user) {
                  $q->where('acesso', 'specific')
                    ->whereHas('users', function ($u) use ($user) {
                        $u->where('user_id', $user->id);
                    });
              });
        });
    }

   // App/Models/Vista.php
public function apply($query)
{
    $conditions = $this->filtros['conditions'] ?? [];
    if (empty($conditions)) return $query;

    $logic = strtolower($this->logica ?? 'and');

    $query->where(function ($q) use ($conditions, $logic) {

        foreach ($conditions as $condition) {
            $field    = $condition['field'] ?? null;
            $value    = $condition['value'] ?? null;
            $operator = $condition['operator'] ?? '=';

            if (!$field || $value === null || $value === '') continue;

            $method = $logic === 'or' ? 'orWhere' : 'where';

            // Campos diretos da tabela recados
            $directColumns = [
                'id','contact_client','plate','estado_id',
                'tipo_formulario_id','sla_id','tipo_id',
                'origem_id','setor_id','departamento_id',
                'aviso_id','campanha_id'
            ];

            if (in_array($field, $directColumns)) {
                if (strtolower($operator) === 'like') {
                    $q->$method($field, 'like', "%{$value}%");
                } else {
                    $q->$method($field, $operator, $value);
                }
                continue;
            }

            // Campos por relacionamento (ex: estado.name)
            if (str_contains($field, '.')) {
                [$relation, $column] = explode('.', $field, 2);

                $q->$method(function ($sub) use ($relation, $column, $operator, $value) {
                    $sub->whereHas($relation, function ($r) use ($column, $operator, $value) {
                        if (strtolower($operator) === 'like') {
                            $r->where($column, 'like', "%{$value}%");
                        } else {
                            $r->where($column, $operator, $value);
                        }
                    });
                });
            }
        }

    });

    return $query;
}



}
