<?php

namespace Domains\Common\Models\Permission;

use Domains\Common\Exceptions\DomainAuthException;

trait CheckActionPermissionTrait
{
    /**
     * Ensure user does have permission to perform action
     * @param ActionPermission          $permission
     * @param ActionContext             $context
     * @throws DomainAuthException
     */
    public static function checkPermission(ActionPermission $permission, ActionContext $context): void
    {
        if (!self::isAllowedTo($permission, $context)) {
            throw DomainAuthException::fromPermission($permission);
        }
    }

    /**
     * Check if user has permission to perform action
     * @param ActionPermission $permission
     * @param ActionContext    $context
     * @return bool
     */
    public static function isAllowedTo(ActionPermission $permission, ActionContext $context): bool
    {
        return $context->performedBy()
            ->role()
            ->hasPermission(
                $permission,
                $context
            );
    }

    /**
     * Check user permission to entity
     * @param ActionPerformedByContract $performedBy
     * @param string                    $permissionsGroupName
     * @return array
     */
    public function allowedActions(ActionPerformedByContract $performedBy, string $permissionsGroupName): array
    {
        $allowed = [];
        $permissions = $performedBy->role()->permissionsGroup($permissionsGroupName);
        foreach($permissions as $permission) {
            if ($performedBy->role()->hasPermission($permission, new ActionContext($performedBy, $this))) {
                $allowed[] = $permission->name();
            }
        }

        return $allowed;
    }
}
