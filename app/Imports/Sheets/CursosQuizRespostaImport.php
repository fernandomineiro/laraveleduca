<?php

namespace App\Imports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class CursosQuizRespostaImport implements ToCollection, WithHeadingRow{
    
    public function collection(Collection $rows){
        
        return $rows;
        
    }
    
    
}