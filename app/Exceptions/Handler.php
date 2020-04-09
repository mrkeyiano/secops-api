<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        // Log other errors except validations
        if (! $exception instanceof ValidationException) {
            Log::error($exception->getMessage());
        }

        if ($exception instanceof ModelNotFoundException) {
            return Response::json([
                'status' => config('secops.status.not_found'),
                'statuscode' => config('secops.code.not_found'),
                'message' => __('errors.no_model'),
            ]);
        }

        if ($exception instanceof NotFoundHttpException) {
            return Response::json([
                'status' => config('secops.status.not_found'),
                'statuscode' => config('secops.code.not_found'),
                'message' => __('errors.not_found'),
            ]);
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return Response::json([
                'status' => config('secops.status.failed'),
                'statuscode' => config('secops.code.not_allowed'),
                'message' => __('errors.method_not_allowed'),
            ]);
        }

        // Catch validation errors
        if ($exception instanceof ValidationException) {
            return Response::json([
                'status' => config('secops.status.failed'),
                'statuscode' => config('secops.code.failed'),
                'message' => __('errors.failed_validation'),
                'validationErrors' => $exception->validator->errors(),
            ]);
        }

        if (! empty($exception->getMessage())) {
            return Response::json([
                'status' => config('secops.status.server_error'),
                'statuscode' => config('secops.code.server_error'),
                'message' => __('errors.server_error'),
                'error' => $exception->getMessage(),
            ]);
        }
        return parent::render($request, $exception);
    }
}
