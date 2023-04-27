<?php

namespace App\Http\Responses\Accounts;

use App\Http\Mappers\Accounts\ApiAuthToken;
use Domains\Common\Models\Auth\AuthToken;
use Illuminate\Http\JsonResponse;

class AuthenticateResponse extends JsonResponse
{
    /**
     * @param AuthToken $token
     */
    public function __construct(AuthToken $token)
    {
        $apiToken = new ApiAuthToken($token);
        parent::__construct(
            $apiToken->toArray(),
            self::HTTP_OK
        );
    }
}
