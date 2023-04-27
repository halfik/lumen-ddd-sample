<?php

namespace App\Http\Middleware;

/**
 * Trim request data
 */
class TrimStringsMiddleware extends TransformsRequestMiddleware
{
    /**
     * The attributes that should not be trimmed.
     *
     * @var array
     */
    protected array $except = [
        'password',
        'password_confirmation',
    ];

    /**
     * Trim
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    protected function transform(string $key, mixed $value): mixed
    {
        if (in_array($key, $this->except, true)) {
            return $value;
        }

        return is_string($value) ? trim($value) : $value;
    }
}
