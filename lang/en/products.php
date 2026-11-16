<?php

return [
    'title' => 'Products',
    'singular' => 'Product',
    'create' => 'New product',
    'edit' => 'Edit product',
    'subtitle' => 'Variety, stem length in centimetres and stems per bunch',
    'fields' => [
        'variety' => 'Variety',
        'stem_length_cm' => 'Length (cm)',
        'stems_per_bunch' => 'Stems per bunch',
        'is_active' => 'Active',
        'label' => 'Product',
    ],
    'validation' => [
        'duplicate' => 'A product with that variety, length and stems per bunch already exists.',
    ],
    'messages' => [
        'created' => 'Product created.',
        'updated' => 'Product updated.',
        'deleted' => 'Product deleted.',
    ],
];
