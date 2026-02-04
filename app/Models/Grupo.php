<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Grupo extends Model
{
    protected $fillable = ['name'];
    use Auditable;


public function users()
{
    return $this->belongsToMany(User::class, 'grupo_user', 'grupo_id', 'user_id');
}

public function recados()
{
    return $this->belongsToMany(Recado::class, 'recado_grupo');
}



}

