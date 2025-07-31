<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
 use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
   
public function render($request, Throwable $exception)
{
    // handle API requests
    if ($request->expectsJson()) {

        // نموذج غير موجود
        if ($exception instanceof ModelNotFoundException) {
            return response()->json([
                'status' => false,
                'message' => 'Resource not found',
                'error' => 'The requested record does not exist.'
            ], 404);
        }

        // رابط أو Route غير موجود
        if ($exception instanceof NotFoundHttpException) {
            return response()->json([
                'status' => false,
                'message' => 'Route not found',
                'error' => 'The requested endpoint does not exist.'
            ], 404);
        }

        // أخطاء أخرى غير متوقعة
        // return response()->json([
        //     'status' => false,
        //     'message' => 'Unexpected error occurred',
        //     'error' => $exception->getMessage()
        // ], 500);
    }

    return parent::render($request, $exception);
}

    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }
}
