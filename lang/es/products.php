<?php

return [
    'title' => 'Productos',
    'singular' => 'Producto',
    'create' => 'Nuevo producto',
    'edit' => 'Editar producto',
    'subtitle' => 'Variedad, largo en centímetros y tallos por bonche',
    'fields' => [
        'variety' => 'Variedad',
        'stem_length_cm' => 'Largo (cm)',
        'stems_per_bunch' => 'Tallos por bonche',
        'is_active' => 'Activo',
        'label' => 'Producto',
    ],
    'validation' => [
        'duplicate' => 'Ya existe un producto con esa variedad, largo y tallos por bonche.',
    ],
    'messages' => [
        'created' => 'Producto creado.',
        'updated' => 'Producto actualizado.',
        'deleted' => 'Producto eliminado.',
    ],
];
