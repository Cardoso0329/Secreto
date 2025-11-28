<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Destinatario;



class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;


    protected $fillable = [
    'name',
    'email',
    'password',
    'cargo_id',
    'visibilidade_recados'
];


    protected $hidden = ['password', 'remember_token'];

    public function isAdmin()
{
    return $this->role === 'admin';
}

    public function destinatario()
{
    return $this->hasOne(Destinatario::class);
}

public function cargo()
{
    return $this->belongsTo(Cargo::class);
}

public function grupos()
{
    return $this->belongsToMany(Grupo::class);
}

public function recados()
{
    return $this->belongsToMany(Recado::class, 'recado_user');
}




    protected static function boot()
{
    parent::boot();

    static::created(function ($user) {
        $destinatario = \App\Models\Destinatario::where('email', $user->email)->first();
        if ($destinatario) {
            $destinatario->user_id = $user->id;
            $destinatario->save();
        } else {
            \App\Models\Destinatario::create([
                'name' => $user->name,
                'email' => $user->email,
                'user_id' => $user->id,
            ]);
        }
    });
}


public function departamentos()
{
    return $this->belongsToMany(Departamento::class, 'departamento_user');
}





    }

    




