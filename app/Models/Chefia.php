<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Chefia extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function recados()
    {
        return $this->hasMany(Recado::class, 'chefia_id');
    }
}
