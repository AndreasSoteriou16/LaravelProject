<?php

namespace App\Http\Controllers;

use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'LaravelProject API',
    description: 'Task management API secured with Laravel Sanctum.'
)]
#[OA\SecurityScheme(
    securityScheme: 'sanctum',
    type: 'apiKey',
    in: 'cookie',
    name: 'laravel_session',
    description: 'Session-based auth via Laravel Sanctum. Log in through /api/login first (browser/Swagger UI must send cookies).'
)]
abstract class Controller
{
    //
}
