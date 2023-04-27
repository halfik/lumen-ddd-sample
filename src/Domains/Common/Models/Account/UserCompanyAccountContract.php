<?php

namespace Domains\Common\Models\Account;

use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\Role;

interface UserCompanyAccountContract
{
    public const SYSTEM_ENTITY_ID = 'c824e919-ad2c-4839-8ab4-098d48e11cfd';

    /**
     * @return AggregateRootId
     */
    public function uuid(): AggregateRootId;

    /**
     * @return CompanyAccountContract
     */
    public function companyAccount(): CompanyAccountContract;

    /**
     * @return UserContract
     */
    public function user(): UserContract;

    /**
     * @return Role
     */
    public function role(): Role;
}
