<?php

namespace Domains\Accounts\Models\Permission\ActionPermissions\Accounts;

use Domains\Common\Models\Permission\ActionPermission;
use Domains\Accounts\Models\Permission\ActionRestrictions\PerformedBy;
use Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;
use Domains\Common\Models\Permission\PermissionsContract;

class ChangeUserRole extends ActionPermission
{
    public function __construct(array $restrictions = [])
    {
        $restrictions = array_merge($restrictions, [
            new PerformedOn\NotCompanyOwner(),
            new PerformedBy\GrantRoleRestriction(),
        ]);

        parent::__construct(
            PermissionsContract::COMPANY_ACCOUNT_CHANGE_USER_ROLE,
            $restrictions
        );
    }

    public static function limitByRoles(array $roles): self
    {
        return new self([
            new PerformedOn\LimitedToRoles($roles),
        ]);
    }
}
