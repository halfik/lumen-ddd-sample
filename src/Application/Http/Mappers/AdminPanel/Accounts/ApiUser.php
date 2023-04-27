<?php

namespace App\Http\Mappers\AdminPanel\Accounts;

use Domains\AdminPanel\Models\Accounts\User;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="AdminPanelUser",
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
 *       property="first_name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="last_name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="display_name",
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
 *  * @OA\Schema(
 *     schema="AdminPanelUserEdit",
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
 *       property="email",
 *       type="string",
 *     ),
 * ),
 */
class ApiUser
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function toArray(): array
    {
        return [
            'id' => (string)$this->user->uuid(),
            'email' => $this->user->email(),
            'first_name' => $this->user->firstName(),
            'last_name' => $this->user->lastName(),
            'display_name' => $this->user->displayName(),
            'created_at' => $this->user->createdAt()->getTimestamp(),
            'updated_at' => $this->user->updatedAt()->getTimestamp(),
        ];
    }
}
