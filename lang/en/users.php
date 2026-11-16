<?php

return [
    'title' => 'Users',
    'singular' => 'User',
    'create' => 'New user',
    'edit' => 'Edit user',
    'subtitle' => 'Back office accounts. There is no public sign up.',
    'fields' => [
        'name' => 'Name',
        'email' => 'Email',
        'password' => 'Password',
        'password_confirmation' => 'Repeat password',
        'role' => 'Role',
        'companies' => 'Companies',
        'locale' => 'Language',
        'is_active' => 'Active',
    ],
    'password_hint' => 'Leave it empty to keep the current one.',
    'messages' => [
        'created' => 'User created.',
        'updated' => 'User updated.',
        'status_changed' => 'User status updated.',
        'password_changed' => 'Password updated.',
    ],
];
