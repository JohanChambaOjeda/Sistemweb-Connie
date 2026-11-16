<?php

return [
    'title' => 'Usuarios',
    'singular' => 'Usuario',
    'create' => 'Nuevo usuario',
    'edit' => 'Editar usuario',
    'subtitle' => 'Cuentas del back office. No hay registro público.',
    'fields' => [
        'name' => 'Nombre',
        'email' => 'Correo',
        'password' => 'Contraseña',
        'password_confirmation' => 'Repetir contraseña',
        'role' => 'Rol',
        'companies' => 'Empresas',
        'locale' => 'Idioma',
        'is_active' => 'Activo',
    ],
    'password_hint' => 'Déjala vacía para no cambiarla.',
    'messages' => [
        'created' => 'Usuario creado.',
        'updated' => 'Usuario actualizado.',
        'status_changed' => 'Estado del usuario actualizado.',
        'password_changed' => 'Contraseña actualizada.',
    ],
];
