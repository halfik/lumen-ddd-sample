<?php

namespace Domains\Accounts\Models\Permission\ActionPermissions\Accounts;

use Domains\Common\Models\Permission\ActionPermission;
use Domains\Common\Models\Permission\PermissionsContract;

class ListCompanyUsers extends ActionPermission
{
    public function __construct()
    {
        parent::__construct(
            PermissionsContract::COMPANY_ACCOUNT_LIST_USERS,
            []
        );
    }
}
