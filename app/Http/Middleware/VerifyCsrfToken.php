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
        'telescope/*',
        'api/v1/auth/google/*',
        'api/v1/web/complaints',
        'api/v1/temporary-files',
        'api/v1/temporary-files/*',
    ];
}
