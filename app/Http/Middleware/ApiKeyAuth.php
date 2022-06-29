<?php

namespace App\Http\Middleware;

use App\Models\Okapi\ApiKey;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;

class ApiKeyAuth
{
    protected const BAD_API_KEY_MESSAGE = 'Bad API key provided';

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return Response|RedirectResponse
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response|RedirectResponse
    {
        if ($request->header('x-api-key')) {
            $headers = explode('|', $request->header('x-api-key'));

            if (count($headers) !== 2) {
                throw new AuthenticationException(self::BAD_API_KEY_MESSAGE);
            }

            [$id, $key] = $headers;

            if (is_numeric($id) === false ||
                (($apiKey = ApiKey::query()->find($id)) === null)) {
                throw new AuthenticationException(self::BAD_API_KEY_MESSAGE);
            }

            if (Hash::check($key, $apiKey->token) === false) {
                throw new AuthenticationException(self::BAD_API_KEY_MESSAGE);
            }

            $request->apiKey = $apiKey;
        }

        return $next($request);
    }
}
