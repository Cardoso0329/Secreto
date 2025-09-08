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
            return [
                'ID' => $recado->id,
                'Cliente' => $recado->contact_client,
                'Matrícula' => $recado->plate,
                'Estado' => $recado->estado->name ?? '',
                'Tipo Formulário' => $recado->tipoFormulario->name ?? '',
                'Mensagem' => $recado->mensagem,
                'Data Abertura' => $recado->abertura,
                'Data Término' => $recado->termino,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'ID',
            'Cliente',
            'Matrícula',
            'Estado',
            'Tipo Formulário',
            'Mensagem',
            'Data Abertura',
            'Data Término',
        ];
    }
}
