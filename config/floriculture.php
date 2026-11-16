<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Temporadas pico
    |--------------------------------------------------------------------------
    |
    | Rangos (mes-día) que definen cada temporada. ResolvePrice los usa para
    | saber si una fecha cae en San Valentín o Día de la Madre.
    |
    */

    'seasons' => [
        'valentines' => ['from' => '01-01', 'to' => '02-28'],
        'mothers_day' => ['from' => '04-01', 'to' => '05-31'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Valores por defecto del producto
    |--------------------------------------------------------------------------
    */

    'default_stems_per_bunch' => 25,

    'stem_length_range' => ['min' => 30, 'max' => 120],

];
