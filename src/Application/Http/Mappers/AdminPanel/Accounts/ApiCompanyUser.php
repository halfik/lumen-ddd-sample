<?php

namespace App\Http\Mappers\AdminPanel\Accounts;

use App\Http\Mappers\AdminPanel\Sales\ApiWorkflow;
use App\Http\Mappers\Common\ApiRole;
use Domains\AdminPanel\Models\Accounts\UserCompanyAccount;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="AdminPanelCompanyUser",
 *   type="object",
 *    allOf={
 *      @OA\Schema(ref="#/components/schemas/AdminPanelUser"),
 *      @OA\Schema(
 *          @OA\Property(
 *             property="status",
 *             type="string"
 *          ),
 *          @OA\Property(
 *              property="company_account",
 *              @OA\Property(ref="#/components/schemas/AdminPanelCompanyAccount")
 *          ),
 *          @OA\Property(
 *              property="role",
 *              @OA\Property(ref="#/components/schemas/Role")
 *          ),
 *          @OA\Property(
 *              property="workflows",
 *              type="array",
 *              @OA\Items(ref="#/components/schemas/AdminPanelWorkflow")
 *          ),
 *       )
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="AdminPanelCompanyUsersList",
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
 *      @OA\Items(ref="#/components/schemas/AdminPanelCompanyUser")
 *   ),
 * ),
 *
 * @OA\Schema(
 *   schema="AdminPanelCompanyUserEdit",
 *   type="object",
 *   allOf={
 *      @OA\Schema(ref="#/components/schemas/AdminPanelUserEdit"),
 *      @OA\Schema(
 *          @OA\Property(
 *             property="role",
 *             type="string"
 *          ),
 *      ),
 *   }
 * )
 */
class ApiCompanyUser
{
    private UserCompanyAccount $companyUser;

    /**
     * @param UserCompanyAccount $companyUser
     */
    public function __construct(UserCompanyAccount $companyUser)
    {
        $this->companyUser = $companyUser;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $apiUser = new ApiUser($this->companyUser->user());
        $apiCompanyAccount = new ApiCompanyAccount($this->companyUser->companyAccount());
        $apiRole = new ApiRole($this->companyUser->role());

        $workflowsData = [];
        foreach ($this->companyUser->companyAccount()->workflows() as $workflow) {
            $apiWorkflow = new ApiWorkflow($workflow);
            $workflowsData[] = $apiWorkflow->toArray();
        }

        $row = $apiUser->toArray();
        $row['company_account'] = $apiCompanyAccount->toArray();
        $row['status'] = $this->companyUser->status();
        $row['role'] = $apiRole->toArray();
        $row['workflows'] = $workflowsData;

        return $row;
    }
}
