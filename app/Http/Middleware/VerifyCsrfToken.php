<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        'ajax/*',
        'pay_response',
        'pay_cancel',
        'pay_now/*',
        'pay_now',
        'api/*'
    ];
}