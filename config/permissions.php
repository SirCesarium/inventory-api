<?php

return [
    'products' => ['*', 'read', 'create', 'update', 'delete'],
    'categories' => ['*', 'read', 'create', 'update', 'delete'],
    'users' => ['*', 'read', 'create', 'update', 'delete'],
    'roles' => ['*', 'read', 'create', 'update', 'delete'],
    'permissions' => ['*', 'read'],
    'audits' => ['*', 'read'],
    'movements' => ['*', 'read', 'create'],
];
