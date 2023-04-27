<?php

namespace Domains\Sales\Models\Permissions\ActionPermissions;

use Domains\Common\Models\Permission\ActionPermission;
use Domains\Common\Models\Permission\PermissionsContract;

class ViewWorkflow extends ActionPermission
{
    public function __construct()
    {
        parent::__construct(
            PermissionsContract::WORKFLOW_VIEW,
            []
        );
    }
}
