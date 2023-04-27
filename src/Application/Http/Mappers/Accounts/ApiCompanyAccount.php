<?php

namespace App\Http\Mappers\Accounts;

use Domains\Accounts\Models\Company\CompanyAccount;

/**
 *  @OA\Schema (
 *     schema="CompanyAccount",
 *     type="object",
 *     @OA\Property (
 *       property="id",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="is_active",
 *       type="boolean",
 *     ),
 *     @OA\Property (
 *       property="created_at",
 *       type="integer",
 *     ),
 *     @OA\Property (
 *       property="updated_at",
 *       type="integer",
 *     )
 * )
 */
class ApiCompanyAccount
{
    private CompanyAccount $companyAccount;

    public function __construct(CompanyAccount $companyAccount)
    {
        $this->companyAccount = $companyAccount;
    }

    public function toArray(): array
    {
        return [
            'id' => (string)$this->companyAccount->uuid(),
            'name' => $this->companyAccount->name(),
            'is_active' => $this->companyAccount->isActive(),
            'created_at' => $this->companyAccount->createdAt()->getTimestamp(),
            'updated_at' => $this->companyAccount->updatedAt()->getTimestamp(),
        ];
    }
}
