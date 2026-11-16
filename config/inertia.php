<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Server side rendering
    |--------------------------------------------------------------------------
    |
    | Hostinger es hosting compartido: no hay procesos persistentes, así que el
    | render en servidor queda apagado.
    |
    */

    'ssr' => [
        'enabled' => false,
        'url' => 'http://127.0.0.1:13714',
    ],

    /*
    |--------------------------------------------------------------------------
    | Pruebas
    |--------------------------------------------------------------------------
    |
    | Las páginas viven en resources/js/pages (en minúscula), como el starter
    | kit de React, así que hay que decírselo al buscador de las pruebas.
    |
    */

    'testing' => [
        'ensure_pages_exist' => true,
        'page_paths' => [
            resource_path('js/pages'),
        ],
        'page_extensions' => [
            'js',
            'jsx',
            'svelte',
            'ts',
            'tsx',
            'vue',
        ],
    ],

];
