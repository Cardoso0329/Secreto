<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;


class SLA extends Model
{
    use Auditable;
      use HasFactory;

      protected $table = 'slas';

    protected $fillable = ['name'];
}
