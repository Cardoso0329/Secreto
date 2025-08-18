<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class TipoFormulario extends Model {

    use HasFactory;

    protected $table = 'tipo_formularios';

    protected $fillable = ['name'];

    public function recados()
{
    return $this->hasMany(Recado::class, 'tipo_formulario_id');
}

}
