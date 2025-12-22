<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campanha extends Model
{
    use HasFactory;

    // Se quiseres permitir preenchimento em massa
    protected $fillable = [
        'name',
    ];

    /**
     * Relação many-to-many com Departamentos
     */
    public function recados()
{
    return $this->belongsToMany(Recado::class, 'recado_campanha');
}

public function departamentos()
{
    return $this->belongsToMany(Departamento::class, 'campanha_departamento');
}



}
