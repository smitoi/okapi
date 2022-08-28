<?php

namespace App\Http\Controllers\Okapi\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApiController extends Controller
{
    /**
     * @param array $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    public function jsonSuccess(array $data, string $message = 'success', int $code = 200): JsonResponse
    {
        return response()->json(
            array_filter([
                'success' => true,
                'message' => $message,
                'data' => $data,
            ]),
            $code,
        );
    }

    /**
     * @param array|null $errors
     * @param string $message
     * @param integer $code
     * @return JsonResponse
     */
    public function jsonError(?array $errors = null, string $message = 'error', int $code = 500): JsonResponse
    {
        return response()->json(
            array_filter([
                'message' => $message,
                'errors' => $errors,
            ]) + ['success' => false],
            $code,
        );
    }
}
