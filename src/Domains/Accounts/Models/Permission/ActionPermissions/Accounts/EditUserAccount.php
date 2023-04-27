<?php

namespace Domains\Accounts\Models\Permission\ActionPermissions\Accounts;

use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;
use Domains\Common\Models\Permission\ActionPermission;
use Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn\OnlyWhenAccountStatus;
use Domains\Common\Models\Permission\PermissionsContract;

class EditUserAccount extends ActionPermission
{
    public function __construct(array $restrictions = [])
    {
        $restrictions = array_merge($restrictions, [
            new OnlyWhenAccountStatus([
                UserCompanyAccountStatus::active(),
                UserCompanyAccountStatus::pending(),
                UserCompanyAccountStatus::inactive(),
            ]),
        ]);
        parent::__construct(
            PermissionsContract::COMPANY_ACCOUNT_EDIT_USER,
            $restrictions
        );
    }

    public static function onlyOwnAccount(): self
    {
        return new self([
            new PerformedOn\OnlyOwnAccount(),
        ]);
    }

    public static function limitByRoles(array $roles): self
    {
        return new self([
            new PerformedOn\LimitedToRoles($roles),
        ]);
    }
}
