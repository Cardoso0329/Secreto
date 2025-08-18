<?php

namespace App\Exports;

use App\Models\Recado;
use Maatwebsite\Excel\Concerns\FromCollection;

class RecadosExport implements FromCollection
{
    public function collection()
    {
        return Recado::all();
    }
}
