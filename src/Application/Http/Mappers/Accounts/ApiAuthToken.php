<?php

namespace App\Http\Mappers\Accounts;

use Domains\Common\Models\Auth\AuthToken;

/**
 *  @OA\Schema (
 *     schema="AuthToken",
 *     type="object",
 *     @OA\Property (
 *       property="access_token",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="token_type",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="expires_at",
 *       type="integer",
 *     )
 * )
 */
class ApiAuthToken
{
    private AuthToken $token;

    public function __construct(AuthToken $token)
    {
        $this->token = $token;
    }

    public function toArray(): array
    {
        return [
            'access_token' => $this->token->token(),
            'token_type' => $this->token->type(),
            'expires_at' => $this->token->expiresAt(),
        ];
    }
}
