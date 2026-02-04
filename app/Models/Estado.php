<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;

class Estado extends Model
{
    use  Auditable;
    use HasFactory;
    
    protected $table = 'estados';

  protected $fillable = ['name'];
}
