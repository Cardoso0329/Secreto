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
                'ID'              => $recado->id,
                'Cliente'         => $recado->contact_client,
                'Matrícula'       => $recado->plate,
                'Email Operador'  => $recado->operator_email,
                'Estado'          => $recado->estado->name ?? '',
                'Tipo Formulário' => $recado->tipoFormulario->name ?? '',
                'SLA'             => $recado->sla->name ?? '',
                'Setor'           => $recado->setor->name ?? '',
                'Departamento'    => $recado->departamento->name ?? '',
                'Origem'          => $recado->origem->name ?? '',
                'Aviso'           => $recado->aviso->name ?? '',
                'Tipo'            => $recado->tipo->name ?? '',
                'Mensagem'        => $recado->mensagem,
                'Ficheiro'        => $recado->ficheiro,
                'Data Abertura'   => $recado->abertura,
                'Data Término'    => $recado->termino,
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
        ];
    }
}
