<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecadoGuestToken extends Model
{
    protected $fillable = ['recado_id', 'token', 'expires_at', 'is_active'];

    protected $casts = [
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function recado()
    {
        return $this->belongsTo(Recado::class);
    }

    public function isValid()
    {
        return $this->is_active && $this->expires_at->isFuture();
    }
}
