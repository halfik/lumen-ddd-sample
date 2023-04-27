<?php

namespace Domains\Accounts\Models\Permission\ActionPermissions\Accounts;

use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Common\Models\Permission\ActionPermission;
use Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;
use Domains\Common\Models\Permission\PermissionsContract;

class ActivateUserCompanyAccount extends ActionPermission
{
    public function __construct(array $restrictions = [])
    {
        $restrictions = array_merge($restrictions, [
            new PerformedOn\OnlyWhenAccountStatus([
                UserCompanyAccountStatus::pending(),
                UserCompanyAccountStatus::inactive()
            ]),
        ]);
        parent::__construct(PermissionsContract::COMPANY_ACCOUNT_ACTIVATE_USER, $restrictions);
    }

    public static function onlyOwnAccount(): self
    {
        return new self([
            new PerformedOn\OnlyOwnAccount(),
        ]);
    }
}
