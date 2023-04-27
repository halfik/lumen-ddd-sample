<?php

namespace Domains\Common\Models;

use Illuminate\Support\Str;

class Slug
{
    public static function generate(string $value): string
    {
        return Str::slug($value);
    }
}
