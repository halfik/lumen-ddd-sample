<?php

namespace App\Http\Middleware;

class ConvertStringBooleans extends TransformsRequestMiddleware
{
    /**
     * Convert string boolean to boolean value
     *
     * @param string $key
     * @param mixed  $value
     * @return mixed
     */
    protected function transform(string $key, mixed $value): mixed
    {
        if (in_array(strtolower($value), ['true', 'false'], true)) {
            return filter_var($value, FILTER_VALIDATE_BOOLEAN);
        }

        return $value;
    }
}
