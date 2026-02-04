<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;
use App\Traits\Auditable;



class Destinatario extends Model
{
    use Auditable;
    use HasFactory;

    protected $table = 'destinatarios';

  protected $fillable = ['name', 'email'];

  public function user()
{
    return $this->belongsTo(User::class);
}

  protected static function boot()
{
    parent::boot();

    static::creating(function ($destinatario) {
        $user = \App\Models\User::where('email', $destinatario->email)->first();
        if ($user) {
            $destinatario->user_id = $user->id;
        }
    });
}


}
