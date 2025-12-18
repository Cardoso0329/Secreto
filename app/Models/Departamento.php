<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Departamento extends Model
{
    use HasFactory;

    protected $table = 'departamentos';

    protected $fillable = ['name'];

    /**
     * Relação muitos-para-muitos com Users
     */
    public function campanhas()
{
    return $this->belongsToMany(Campanha::class, 'campanha_departamento');
}

// app/Models/Departamento.php
public function users()
{
    return $this->belongsToMany(User::class, 'departamento_user');
}


}
