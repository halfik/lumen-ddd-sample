<?php

namespace App\Http\Middleware;

class ConvertEmptyStringsToNull extends TransformsRequestMiddleware
{
    /**
     * Trim
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    protected function transform(string $key, mixed $value): mixed
    {
        return $value === '' ? null : $value;
    }
}
