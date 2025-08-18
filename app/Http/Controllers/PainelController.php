<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Destinatario;
use App\Models\SLA;
use App\Models\Departamento;
use App\Models\Origem;
use App\Models\Aviso;
use App\Models\Estado;
use App\Models\Setor;
use App\Models\Tipo;


class PainelController extends Controller
{
    public function index()
    { 
        
        return view('painel.index', [
            'destinatarios' => Destinatario::all(),
            'slas' => SLA::all(),
            'departamentos' => Departamento::all(),
            'origens' => Origem::all(),
            'setores' => Setor::all(),
            'estados' => Estado::all(),
            'avisos' => Aviso::all(),
            'tipos' => Tipo::all(),


        ]);
    }
}

