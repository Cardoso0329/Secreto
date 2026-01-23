<?php

namespace App\Models;

use Illuminate\Support\Str;
use App\Models\RecadoGuestToken;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Recado extends Model
{
    use HasFactory;

        protected $casts = [
    'abertura' => 'datetime',
    'termino' => 'datetime',];

    // Adicione o setor_id, origem_id, departamento_id, etc., aqui
   protected $fillable = [
    'name', 'contact_client', 'operator_email', 'sla_id', 'tipo_id', 
    'origem_id', 'setor_id', 'departamento_id', 'mensagem', 'ficheiro',
    'aviso_id', 'estado_id', 'observacoes', 'abertura', 'termino',
    'tipo_formulario_id', 'wip', 'destinatario_livre', 'plate', 'user_id', 'assunto',  'campanha_id' 
    
];


public function setor() { return $this->belongsTo(Setor::class); }
public function origem() { return $this->belongsTo(Origem::class); }
public function departamento()
{
    return $this->belongsTo(Departamento::class, 'departamento_id');
}
public function destinatarios() { return $this->belongsToMany(User::class, 'recado_user', 'recado_id', ); }
public function estado() { return $this->belongsTo(Estado::class); }
public function sla() { return $this->belongsTo(SLA::class); }
public function tipo() { return $this->belongsTo(Tipo::class); }
public function aviso() { return $this->belongsTo(Aviso::class); }
public function tipoFormulario()
{
    return $this->belongsTo(TipoFormulario::class, 'tipo_formulario_id');
}

public function guestTokens()
{
    return $this->hasMany(RecadoGuestToken::class);
}

public function grupos()
{
    return $this->belongsToMany(Grupo::class, 'recado_grupo', 'recado_id', 'grupo_id');
}

public function scopeFilter($query, $filters)
{
    if (!empty($filters['id'])) {
        $query->where('id', $filters['id']);
    }

    if (!empty($filters['contact_client'])) {
        $query->where('contact_client', 'like', '%' . $filters['contact_client'] . '%');
    }

    if (!empty($filters['plate'])) {
        $query->where('plate', 'like', '%' . $filters['plate'] . '%');
    }

    if (!empty($filters['estado_id'])) {
        $query->where('estado_id', $filters['estado_id']);
    }

    if (!empty($filters['tipo_formulario_id'])) {
        $query->where('tipo_formulario_id', $filters['tipo_formulario_id']);
    }

    return $query;
}
public function destinatariosUsers()
{
    return $this->belongsToMany(User::class, 'recado_user', 'recado_id', 'user_id');
}

public function destinatariosLivres()
{
    return $this->hasMany(Destinatario::class);
}

public function campanha()
{
    return $this->belongsTo(Campanha::class, 'campanha_id');
}

public function chefia()
{
    return $this->belongsTo(\App\Models\Chefia::class, 'chefia_id');
}

public function avisosEnviados()
{
    return $this->belongsToMany(Aviso::class, 'recado_aviso')->withTimestamps();
}




}
