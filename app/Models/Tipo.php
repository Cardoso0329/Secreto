<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Tipo extends Model
{

          use HasFactory;


    protected $table = 'tipos';
    protected $fillable = ['name'];

}
