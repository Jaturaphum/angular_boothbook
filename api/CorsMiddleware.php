<?php

namespace App\Middleware;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as Handler;

class CorsMiddleware
{
    public function __invoke(Request $request, Handler $handler): Response
    {
        $response = $handler->handle($request);

        $response = $response
            ->withHeader('Access-Control-Allow-Origin', '*')
            ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Authorization');

        // Handle OPTIONS requests for preflight
        if ($request->getMethod() === 'OPTIONS') {
            $response = $response->withStatus(200);
        }

        return $response;
    }
}
