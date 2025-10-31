<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class RecadosExport implements FromCollection, WithHeadings
{
    protected $recados;

    public function __construct($recados)
    {
        $this->recados = $recados;
    }

    public function collection()
    {
        return $this->recados->map(function ($recado) {

            // Destinatários Users
            $destinatariosUsers = $recado->destinatarios?->pluck('name')->implode(', ') ?? '';

            // Destinatários Grupos (todos os users dentro de grupos)
            $destinatariosGrupos = $recado->grupos?->flatMap(fn($g) => $g->users)->pluck('name')->unique()->implode(', ') ?? '';

            // Destinatários Livres (emails livres + guest tokens ativos)
            $destinatariosLivresArray = [];

            if (!empty($recado->destinatario_livre)) {
                $destinatariosLivresArray[] = $recado->destinatario_livre;
            }

            if ($recado->guestTokens?->count()) {
                $guestEmails = $recado->guestTokens->pluck('email')->toArray();
                $destinatariosLivresArray = array_merge($destinatariosLivresArray, $guestEmails);
            }

            $destinatariosLivres = implode(', ', $destinatariosLivresArray);

            return [
                'ID'                  => $recado->id,
                'Cliente'             => $recado->contact_client,
                'Matrícula'           => $recado->plate,
                'Email Operador'      => $recado->operator_email,
                'Estado'              => $recado->estado->name ?? '',
                'Tipo Formulário'     => $recado->tipoFormulario->name ?? '',
                'SLA'                 => $recado->sla->name ?? '',
                'Setor'               => $recado->setor->name ?? '',
                'Departamento'        => $recado->departamento->name ?? '',
                'Origem'              => $recado->origem->name ?? '',
                'Aviso'               => $recado->aviso->name ?? '',
                'Tipo'                => $recado->tipo->name ?? '',
                'Mensagem'            => $recado->mensagem,
                'Ficheiro'            => $recado->ficheiro,
                'Data Abertura'       => $recado->abertura,
                'Data Término'        => $recado->termino,
                'Destinatários Users' => $destinatariosUsers,
                'Destinatários Grupos'=> $destinatariosGrupos,
                'Destinatários Livres'=> $destinatariosLivres,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Matrícula',
            'Email Operador',
            'Estado',
            'Tipo Formulário',
            'SLA',
            'Setor',
            'Departamento',
            'Origem',
            'Aviso',
            'Tipo',
            'Mensagem',
            'Ficheiro',
            'Data Abertura',
            'Data Término',
            'Destinatários Users',
            'Destinatários Grupos',
            'Destinatários Livres',
        ];
    }
}
