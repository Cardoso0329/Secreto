<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Origem extends Model
{
    use HasFactory;
    use Auditable;


    protected $table = 'origens';

  protected $fillable = ['name'];
}
