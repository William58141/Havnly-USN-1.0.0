<?php

namespace App\Exceptions;

use App\Exceptions\Api\JsonException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'client_secret',
        'encryption_key',
        'access_token',
        'refresh_token',
        'session_id',
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->renderable(function (Throwable $e, $request) {
            if ($request->wantsJson() || $request->is('api/*')) {
                // 404
                if ($e instanceof NotFoundHttpException) {
                    throw new JsonException(404);
                }
                // 405
                if ($e instanceof MethodNotAllowedHttpException) {
                    throw new JsonException(405);
                }
                // 400 - default:422
                if ($e instanceof ValidationException) {
                    throw new JsonException(400, 'See /help for more info.');
                }
            }
        });
    }
}
