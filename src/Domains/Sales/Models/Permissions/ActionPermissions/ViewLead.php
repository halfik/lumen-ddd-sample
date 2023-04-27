<?php

namespace Domains\Sales\Models\Permissions\ActionPermissions;

use Domains\Common\Models\Permission\ActionPermission;
use Domains\Common\Models\Permission\PermissionsContract;
use Domains\Sales\Models\Permissions\ActionRestrictions\PerformedOn;

class ViewLead extends ActionPermission
{
    public function __construct(array $restrictions = [])
    {
        parent::__construct(
            PermissionsContract::LEAD_VIEW,
            $restrictions
        );
    }

    public static function onlyOwnLead(): self
    {
        return new self([
            new PerformedOn\OnlyOwnLead(),
        ]);
    }
}
