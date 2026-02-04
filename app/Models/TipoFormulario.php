<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;


class TipoFormulario extends Model {

    use HasFactory;
    use Auditable;
    protected $table = 'tipo_formularios';

    protected $fillable = ['name'];

    public function recados()
{
    return $this->hasMany(Recado::class, 'tipo_formulario_id');
}

}
