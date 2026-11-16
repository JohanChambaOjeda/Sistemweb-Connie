<?php

return [
    'title' => 'Importar datos',
    'subtitle' => 'Sube un Excel o CSV, revisa el mapeo de columnas y guarda solo las filas válidas.',
    'types' => [
        'customers' => 'Clientes',
        'varieties' => 'Variedades',
        'products' => 'Productos',
        'prices' => 'Precios',
    ],
    'status' => [
        'uploaded' => 'Subido',
        'mapped' => 'Mapeado',
        'committed' => 'Importado',
        'failed' => 'Con error',
    ],
    'fields' => [
        'type' => 'Qué vas a importar',
        'file' => 'Archivo',
        'header_row' => 'Fila de encabezados',
        'column' => 'Columna del archivo',
        'field' => 'Campo del sistema',
        'example' => 'Ejemplo',
        'row' => 'Fila',
        'errors' => 'Errores',
    ],
    'upload' => 'Subir archivo',
    'mapping' => 'Mapeo de columnas',
    'mapping_hint' => 'El sistema adivina las columnas por el encabezado. Corrige lo que haga falta y guarda.',
    'preview' => 'Vista previa',
    'preview_hint' => 'Se muestran las primeras 50 filas. El resumen cuenta todo el archivo.',
    'rejected_rows' => 'Filas con error',
    'summary' => ':total filas · :valid válidas · :invalid con error',
    'ignore_column' => 'No importar',
    'commit' => 'Importar solo las filas válidas',
    'download_report' => 'Descargar reporte de rechazos',
    'recent_runs' => 'Importaciones recientes',
    'no_runs' => 'Todavía no has importado archivos.',
    'expected_columns' => 'Columnas que espera',
    'required' => 'obligatorio',
    'report' => [
        'row' => 'Fila',
        'reason' => 'Motivo del rechazo',
    ],
    'errors' => [
        'unknown_customer' => 'El cliente :name no existe. Impórtalo primero.',
    ],
    'messages' => [
        'mapping_saved' => 'Mapeo guardado.',
        'committed' => 'Se importaron :imported filas y se rechazaron :rejected.',
        'deleted' => 'Importación eliminada.',
    ],
];
