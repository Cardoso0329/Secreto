<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Origem extends Model
{
    use HasFactory;

    protected $table = 'origens';

  protected $fillable = ['name'];
}
