<?php

namespace App\Exceptions;

use App\Traits\ApiResponse;
use Symfony\Component\HttpFoundation\Response;

class ApiExceptionResponse
{
    use ApiResponse;

    public function __invoke(Response $response): Response
    {
        if (! request()->is('api/*') || $response->getStatusCode() < 400) {
            return $response;
        }

        $body = json_decode($response->getContent(), true) ?? [];
        if (array_key_exists('success', $body)) {
            return $response;
        }

        $status = $response->getStatusCode();
        $message = $status >= 500 ? 'An unexpected error occurred.' : ($body['message'] ?? Response::$statusTexts[$status] ?? 'Request failed.');
        $result = $this->errorResponse($message, $status, $body['errors'] ?? []);
        foreach (['Retry-After', 'X-RateLimit-Limit', 'X-RateLimit-Remaining', 'Allow', 'WWW-Authenticate'] as $header) {
            if ($response->headers->has($header)) {
                $result->headers->set($header, $response->headers->get($header));
            }
        }

        return $result;
    }
}
