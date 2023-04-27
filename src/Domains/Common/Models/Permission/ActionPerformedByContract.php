<?php

namespace Domains\Common\Models\Permission;

use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRootId;

interface ActionPerformedByContract
{
    /**
     * @return AggregateRootId
     */
    public function uuid(): AggregateRootId;

    /**
     * @return CompanyAccountContract
     */
    public function companyAccount(): CompanyAccountContract;

    /**
     * @return Role
     */
    public function role(): Role;

    /**
     * @return UserContract
     */
    public function user(): UserContract;

    /**
     * @param CompanyAccountContract $companyAccount
     * @return bool
     */
    public function isMemberOf(CompanyAccountContract $companyAccount): bool;
}
