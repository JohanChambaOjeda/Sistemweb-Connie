<?php

return [
    'title' => 'Listas de precios',
    'singular' => 'Precio',
    'create' => 'Nuevo precio',
    'edit' => 'Editar precio',
    'subtitle' => 'Precio por tallo en USD. Prioridad: cliente, temporada y luego base.',
    'fields' => [
        'product' => 'Producto',
        'customer' => 'Cliente',
        'season' => 'Temporada',
        'price_per_stem' => 'Precio por tallo',
        'valid_from' => 'Vigente desde',
        'valid_to' => 'Vigente hasta',
        'is_active' => 'Activo',
        'notes' => 'Notas',
    ],
    'seasons' => [
        'valentines' => 'San Valentín',
        'mothers_day' => 'Día de la Madre',
    ],
    'base_price' => 'Precio base',
    'all_year' => 'Todo el año',
    'no_end_date' => 'Sin fecha de fin',
    'messages' => [
        'created' => 'Precio creado.',
        'updated' => 'Precio actualizado.',
        'deleted' => 'Precio eliminado.',
    ],
];
