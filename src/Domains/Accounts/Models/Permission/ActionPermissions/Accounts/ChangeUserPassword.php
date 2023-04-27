<?php

namespace Domains\Accounts\Models\Permission\ActionPermissions\Accounts;

use Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;
use Domains\Common\Models\Permission\ActionPermission;
use Domains\Common\Models\Permission\PermissionsContract;

class ChangeUserPassword extends ActionPermission
{
    /**
     * @param array $restrictions
     */
    public function __construct(array $restrictions = [])
    {
        parent::__construct(PermissionsContract::USER_CHANGE_PASSWORD, $restrictions);
    }

    /**
     * @return static
     */
    public static function onlyOwnAccount(): self
    {
        return new self([
            new PerformedOn\OnlyOwnAccount()
        ]);
    }
}
