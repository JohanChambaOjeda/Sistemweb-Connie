<?php

return [
    'title' => 'Price lists',
    'singular' => 'Price',
    'create' => 'New price',
    'edit' => 'Edit price',
    'subtitle' => 'Price per stem in USD. Priority: customer, season and then base.',
    'fields' => [
        'product' => 'Product',
        'customer' => 'Customer',
        'season' => 'Season',
        'price_per_stem' => 'Price per stem',
        'valid_from' => 'Valid from',
        'valid_to' => 'Valid to',
        'is_active' => 'Active',
        'notes' => 'Notes',
    ],
    'seasons' => [
        'valentines' => 'Valentine\'s Day',
        'mothers_day' => 'Mother\'s Day',
    ],
    'base_price' => 'Base price',
    'all_year' => 'All year',
    'no_end_date' => 'No end date',
    'messages' => [
        'created' => 'Price created.',
        'updated' => 'Price updated.',
        'deleted' => 'Price deleted.',
    ],
];
