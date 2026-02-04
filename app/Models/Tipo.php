<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;


class Tipo extends Model
{

          use HasFactory;
            use Auditable;


    protected $table = 'tipos';
    protected $fillable = ['name'];

}
