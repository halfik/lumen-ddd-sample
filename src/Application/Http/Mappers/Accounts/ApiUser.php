<?php

namespace App\Http\Mappers\Accounts;

use Domains\Common\Models\Account\UserContract;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema (
 *     schema="User",
 *     type="object",
 *     @OA\Property (
 *       property="id",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="email",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="email_verified_at",
 *       type="integer",
 *     ),
 *     @OA\Property (
 *       property="first_name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="last_name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="middle_name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="display_name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="title",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="country_code",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="phone_number",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="created_at",
 *       type="integer",
 *     ),
 *     @OA\Property (
 *       property="updated_at",
 *       type="integer",
 *     )
 * ),
 *
 *  @OA\Schema (
 *     schema="LeadOwner",
 *     type="object",
 *     @OA\Property (
 *       property="id",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="display_name",
 *       type="string",
 *     ),
 *   ),
 *
 *  @OA\Schema (
 *     schema="LeadCreatedBy",
 *     type="object",
 *     @OA\Property (
 *       property="id",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="display_name",
 *       type="string",
 *     ),
 *   ),
 *
 * @OA\Schema (
 *     schema="UserEdit",
 *     type="object",
 *     @OA\Property (
 *       property="first_name",
 *       type="string",
 *     ),
 *      @OA\Property (
 *       property="last_name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="title",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="country_code",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="phone_number",
 *       type="string",
 *     ),
 * ),
 */
class ApiUser
{
    private UserContract $user;

    public function __construct(UserContract $user)
    {
        $this->user = $user;
    }

    public function toArray(): array
    {
        return [
            'id' => (string)$this->user->uuid(),
            'email' => $this->user->email(),
            'email_verified_at' => $this->user->emailVerifiedAt()?->getTimestamp(),
            'first_name' => $this->user->firstName(),
            'last_name' => $this->user->lastName(),
            'display_name' => $this->user->displayName(),
            'title' => $this->user->title(),
            'country_code' => $this->user->countryCode(),
            'phone_number' => $this->user->phoneNumber(),
            'created_at' => $this->user->createdAt()->getTimestamp(),
            'updated_at' => $this->user->updatedAt()->getTimestamp(),
        ];
    }
}
