<?php


namespace App\Traits;


use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

trait HasAlerts
{
    /**
     * Set failed response
     *
     * @param $message
     * @param array $data
     * @return JsonResponse
     */
    public function failedAlert($message, array $data = []): JsonResponse
    {
        $response = [
            'status' => config('secops.status.failed'),
            'statuscode' => config('secops.code.failed'),
            'message' => $message
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response, 200);
    }

    /**
     * Set success response
     *
     * @param $message
     * @param array $data
     * @return JsonResponse
     */
    public function successAlert($message, array $data = []): JsonResponse
    {
        $response = [
            'status' => config('secops.status.success'),
            'statuscode' => config('secops.code.success'),
            'message' => $message
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response, 200);
    }

    /**
     * Set wallet enquiry response
     *
     * @param $message
     * @param array $data
     * @return JsonResponse
     */
    public function walletAlert($message, array $data = []): JsonResponse
    {
        $response = [
            'message' => $message,
            'status' => config('secops.status.success'),
            'statuscode' => config('secops.code.success'),
        ];

        if (! empty($data)) {
            $response = array_merge($response, $data);
        }

        return Response::json($response, 200);
    }

    /**
     * Set server error response
     *
     * @param $message
     * @param Exception|null $exception
     * @return JsonResponse
     */
    public function serverErrorAlert($message, Exception $exception = null): JsonResponse
    {
        if ($exception !== null) {
            Log::error("{$exception->getMessage()} on line {$exception->getLine()} in {$exception->getFile()}"
            );
        }

        $response = [
            'status' => config('secops.status.server_error'),
            'statuscode' => config('secops.code.server_error'),
            'message' => $message
        ];

        return Response::json($response, 500);
    }

    /**
     * Set not found response
     *
     * @param $message
     * @param array $data
     * @return JsonResponse
     */
    public function notFoundAlert($message, array $data = []): JsonResponse
    {
        $response = [
            'status' => config('secops.status.not_found'),
            'statuscode' => config('secops.code.not_found'),
            'message' => $message
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response);
    }

    /**
     * Set not allowed response
     *
     * @param $message
     * @param array $data
     * @return JsonResponse
     */
    public function notAllowedAlert($message, array $data = []): JsonResponse
    {
        $response = [
            'status' => config('secops.status.not_allowed'),
            'statuscode' => config('secops.code.not_allowed'),
            'message' => $message
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response, 302);
    }

    /**
     * Set form validation errors
     *
     * @param $errors
     * @param array $data
     * @return JsonResponse
     */
    public function formValidationAlert($errors, array $data = []): JsonResponse
    {
        $response = [
            'status' => config('secops.status.failed'),
            'statuscode' => config('secops.code.failed'),
            'message' => 'Whoops. Validation failed',
            'validationErrors' => $errors,
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response);
    }

    /**
     * Set not exist response
     *
     * @param $message
     * @param array $data
     * @return JsonResponse
     */
    public function notExistAlert($message, array $data = []): JsonResponse
    {
        $response = [
            'status' => config('secops.status.failed'),
            'statuscode' => config('secops.code.notexist'),
            'message' => $message
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response);
    }

    /**
     * Set exist response
     *
     * @param $message
     * @param array $data
     * @return JsonResponse
     */
    public function existsAlert($message, array $data = []): JsonResponse
    {
        $response = [
            'status' => config('secops.status.failed'),
            'statuscode' => config('secops.code.exists'),
            'message' => $message
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response);
    }

    /**
     * Set insufficient funds response
     *
     * @param $message
     * @param array $data
     * @return JsonResponse
     */
    public function insufficientFundsAlert($message, array $data = []): JsonResponse
    {
        $response = [
            'status' => config('secops.status.failed'),
            'statuscode' => config('secops.code.insufficient'),
            'message' => $message
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response);
    }

    /**
     * Set network error response
     *
     * @param $message
     * @param array $data
     * @return JsonResponse
     */
    public function networkErrorAlert($message, array $data = []): JsonResponse
    {
        $response = [
            'status' => config('secops.status.failed'),
            'statuscode' => config('secops.code.network_error'),
            'message' => $message
        ];

        if (! empty($data)) {
            $response['data'] = $data;
        }

        return Response::json($response);
    }
}
