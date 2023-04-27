<?php

namespace Domains\Accounts\Models\Permission\ActionPermissions\Accounts;

use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Common\Models\Permission\ActionPermission;
use Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;
use Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn\OnlyWhenAccountStatus;
use Domains\Common\Models\Permission\PermissionsContract;

class DeactivateUserCompanyAccount extends ActionPermission
{
    public function __construct()
    {
        parent::__construct(
            PermissionsContract::COMPANY_ACCOUNT_DEACTIVATE_USER,
            [
                new PerformedOn\NotCompanyOwner(),
                new PerformedOn\NotLoggedUser(),
                new OnlyWhenAccountStatus([
                    UserCompanyAccountStatus::active()
                ]),
            ]
        );
    }
}
