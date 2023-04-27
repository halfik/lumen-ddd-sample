<?php

namespace App\Http\Responses\Sales;

use Illuminate\Http\JsonResponse;

class DeleteLeadResponse extends JsonResponse
{
    public function __construct()
    {
        parent::__construct('OK', self::HTTP_NO_CONTENT);
    }
}

