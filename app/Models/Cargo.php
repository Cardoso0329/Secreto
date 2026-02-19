<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Cargo extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = ['name'];
}
