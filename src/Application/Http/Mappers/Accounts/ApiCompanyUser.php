<?php

namespace App\Http\Mappers\Accounts;

use App\Http\Mappers\Common\ApiRole;
use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Accounts\Models\Permission\ActionPermissions;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *   schema="CompanyUser",
 *   type="object",
 *    allOf={
 *      @OA\Schema(ref="#/components/schemas/User"),
 *      @OA\Schema(
 *          @OA\Property(
 *             property="status",
 *             type="string"
 *          ),
 *          @OA\Property(
 *              property="role",
 *              @OA\Property(ref="#/components/schemas/Role")
 *          ),
 *       )
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="CompanyUserEdit",
 *   type="object",
 *   allOf={
 *      @OA\Schema(ref="#/components/schemas/UserEdit"),
 *      @OA\Schema(
 *          @OA\Property(
 *             property="role",
 *             type="string"
 *          ),
 *      ),
 *      @OA\Schema(
 *          @OA\Property(
 *             property="quota_value",
 *             type="integer"
 *          ),
 *      ),
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="CompanyUserWithActions",
 *   type="object",
 *    allOf={
 *      @OA\Schema(ref="#/components/schemas/CompanyUser"),
 *      @OA\Schema(
 *         @OA\Property (
 *             property="allowed_actions",
 *             type="array",
 *             @OA\Items(
 *               type="string",
 *             )
 *         ),
 *      )
 *   }
 * )
 *
 */
class ApiCompanyUser
{
    private const ACTION_PERMISSIONS = [
        ActionPermissions\Accounts\ActivateUserCompanyAccount::class,
        ActionPermissions\Accounts\DeactivateUserCompanyAccount::class,
        ActionPermissions\Accounts\EditUserAccount::class,
    ];

    private UserCompanyAccount $companyUser;

    /**
     * @param UserCompanyAccount $companyUser
     */
    public function __construct(UserCompanyAccount $companyUser)
    {
        $this->companyUser = $companyUser;
    }

    /**
     * @param UserCompanyAccount $loggedUser
     * @return array
     */
    public function allowedActions(UserCompanyAccount $loggedUser): array
    {
        $allowed = [];

        foreach (self::ACTION_PERMISSIONS as $className) {
            $permission = new $className();
            $actionContext = new ActionContext($loggedUser, $this->companyUser);
            if ($loggedUser->role()->hasPermission($permission, $actionContext)) {
                $allowed[] = (string)$permission;
            }
        }

        return $allowed;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $apiUser = new ApiUser($this->companyUser->user());
        $apiRole = new ApiRole($this->companyUser->role());

        $row = $apiUser->toArray();
        $row['status'] = (string)$this->companyUser->status();
        $row['role'] = $apiRole->toArray();

        return $row;
    }
}
