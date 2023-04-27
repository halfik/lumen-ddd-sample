<?php

namespace App\Http\Mappers\AdminPanel\Accounts;

use Domains\AdminPanel\Exceptions\AdminPanelDomainException;
use Domains\AdminPanel\Models\Accounts\CompanyAccount;
use Domains\Common\Exceptions\DomainException;
use OpenApi\Annotations as OA;

/**
 *  @OA\Schema(
 *     schema="AdminPanelCompanyAccount",
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
 *     ),
 *     @OA\Property (
 *       property="owner",
 *       @OA\Property(ref="#/components/schemas/AdminPanelUser")
 *     )
 * ),
 *
 * @OA\Schema(
 *   schema="AdminPanelCompanyAccountsList",
 *   type="object",
 *   @OA\Property(
 *     property="current_page",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="last_page",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="per_page",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="total",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *      property="data",
 *      type="array",
 *      @OA\Items(ref="#/components/schemas/AdminPanelCompanyAccount")
 *   ),
 * ),
 */
class ApiCompanyAccount
{
    private CompanyAccount $companyAccount;

    public function __construct(CompanyAccount $companyAccount)
    {
        $this->companyAccount = $companyAccount;
    }

    /**
     * @return array
     * @throws AdminPanelDomainException
     * @throws DomainException
     */
    public function toArray(): array
    {
        $owner = $this->companyAccount->owner()->user();

        return [
            'id' => (string)$this->companyAccount->uuid(),
            'name' => $this->companyAccount->name(),
            'is_active' => $this->companyAccount->isActive(),
            'created_at' => $this->companyAccount->createdAt()->getTimestamp(),
            'updated_at' => $this->companyAccount->updatedAt()->getTimestamp(),
            'owner' => (new ApiUser($owner))->toArray(),
        ];
    }
}
