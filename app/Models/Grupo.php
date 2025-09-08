<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grupo extends Model
{
    protected $fillable = ['name'];


public function users()
{
    return $this->belongsToMany(User::class, 'grupo_user', 'grupo_id', 'user_id');
}

public function recados()
{
    return $this->belongsToMany(Recado::class, 'recado_grupo');
}



}

