<?php

namespace Domains\Accounts\Models\Permission\ActionPermissions\Accounts;

use Domains\Common\Models\Permission\ActionPermission;
use Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;
use Domains\Common\Models\Permission\PermissionsContract;

class ViewCompanyUserDetails extends ActionPermission
{
    public function __construct(array $restrictions = [])
    {
        parent::__construct(
            PermissionsContract::COMPANY_ACCOUNT_VIEW_USER,
            $restrictions,
            true
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
