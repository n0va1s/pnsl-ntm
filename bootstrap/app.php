<?php

use App\Http\Middleware\OnlyManagerMiddleware;
use App\Http\Middleware\TraceIdMiddleware;
use App\Notifications\SystemExceptionTelegram;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'manager' => OnlyManagerMiddleware::class,
        ]);

        $middleware->append(TraceIdMiddleware::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->reportable(function (Throwable $e) {
            if ($e instanceof \Symfony\Component\HttpKernel\Exception\HttpExceptionInterface && $e->getStatusCode() < 500) {
                return;
            }

            $errorHash = md5($e->getMessage() . $e->getFile() . $e->getLine());
            
            if (!Cache::has('error_notification_' . $errorHash)) {
                $traceId = app()->has('trace_id') ? app('trace_id') : 'N/A';
                $chatId = config('services.telegram-bot-api.chat_id');

                if ($chatId) {
                    Notification::route('telegram', $chatId)
                        ->notify(new SystemExceptionTelegram($e, $traceId));
                }

                Cache::put('error_notification_' . $errorHash, true, now()->addMinutes(5));
            }
        });
    })->create();
