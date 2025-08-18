<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class SLA extends Model
{
      use HasFactory;

      protected $table = 'slas';

    protected $fillable = ['name'];
}
