<?php

namespace App\Http\Responses\Accounts;

use Illuminate\Http\JsonResponse;

class ChangePasswordResponse extends JsonResponse
{
    public function __construct()
    {
        parent::__construct(
            'OK',
            self::HTTP_NO_CONTENT
        );
    }
}
