<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Traits\Auditable;


class Setor extends Model
{
    use HasFactory;
    use Auditable;

      protected $table = 'setores';

    protected $fillable = ['name'];

    public function children()
{
    return $this->hasMany(Setor::class, 'parent_id');
}

}


