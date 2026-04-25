<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Http\Middleware\RequestIdMiddleware;
use App\Http\Middleware\HipaaLoggingMiddleware;
use App\Http\Middleware\ResponseCompressionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/health',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(RequestIdMiddleware::class);
        $middleware->append(HipaaLoggingMiddleware::class);
        $middleware->append(ResponseCompressionMiddleware::class);
        
        $middleware->alias([
            'auth.jwt' => \PHPOpenSourceSaver\JWTAuth\Http\Middleware\Authenticate::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                $status = ($e instanceof HttpExceptionInterface) ? $e->getStatusCode() : 500;
                
                return response()->json([
                    'type' => 'https://api.dentalos.com/errors/' . $status,
                    'title' => class_basename($e),
                    'status' => $status,
                    'detail' => $e->getMessage(),
                    'instance' => $request->fullUrl(),
                ], $status, [
                    'Content-Type' => 'application/problem+json',
                ]);
            }
        });
    })->create();
