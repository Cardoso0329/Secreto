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

public function users()
{
    return $this->hasMany(User::class); // já deve existir
}

}
