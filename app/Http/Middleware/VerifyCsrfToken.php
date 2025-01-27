<?php

namespace App\Http\Middleware;


use Illuminate\Routing\Controllers\Middleware;

class VerifyCsrfToken extends Middleware
{
    public ?array $except = [
        '/bot/webhook'
    ];
}
