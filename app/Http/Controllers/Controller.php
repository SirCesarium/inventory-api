<?php

namespace App\Http\Controllers;

abstract class Controller
{
    protected function perPage(): int
    {
        return max(1, min(100, (int) request()->query('per_page', 15)));
    }
}
