<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;


class Aviso extends Model
{
    
    use Auditable;
    
        use HasFactory;
    
        protected $table = 'avisos';
    
      protected $fillable = ['name'];


      public function recados()
{
    return $this->belongsToMany(Recado::class, 'recado_aviso')->withTimestamps();
}

}
