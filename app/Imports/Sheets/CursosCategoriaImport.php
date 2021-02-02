<?php

namespace App\Imports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CursosCategoriaImport implements ToCollection, WithHeadingRow{
    
    public function collection(Collection $rows){
        
        return $rows;
        
    }
    
    
}