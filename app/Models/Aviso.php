<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Aviso extends Model
{

    
        use HasFactory;
    
        protected $table = 'avisos';
    
      protected $fillable = ['name'];


      public function recados()
{
    return $this->belongsToMany(Recado::class, 'recado_aviso')->withTimestamps();
}

}
