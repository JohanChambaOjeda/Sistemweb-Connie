<?php

namespace App\Support;

use Maatwebsite\Excel\Concerns\ToArray;

/**
 * Importador vacío: solo se usa para que maatwebsite/excel devuelva la hoja
 * tal como está.
 */
class RawSheet implements ToArray
{
    /**
     * @param  array<int, array<int, mixed>>  $array
     */
    public function array(array $array): void
    {
        //
    }
}
