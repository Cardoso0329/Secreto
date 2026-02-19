<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Auditable;

class Chefia extends Model
{
    use HasFactory;
    use Auditable;

    protected $fillable = [
        'name',
    ];

    public function recados()
    {
        return $this->hasMany(Recado::class, 'chefia_id');
    }

    public function users()
{
    return $this->belongsToMany(\App\Models\User::class)->withTimestamps();
}

}
