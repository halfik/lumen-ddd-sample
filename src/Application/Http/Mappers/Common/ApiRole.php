<?php

namespace App\Http\Mappers\Common;

use Domains\Common\Models\Permission\Role;

/**
 *  @OA\Schema (
 *     schema="Role",
 *     type="object",
 *     @OA\Property (
 *       property="name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="permissions",
 *       type="array",
 *       @OA\Items(
 *          type="string",
 *       )
 *     )
 * )
 */
class ApiRole
{
    private Role $role;

    /**
     * @param Role $role
     */
    public function __construct(Role $role)
    {
        $this->role = $role;
    }

    public function toArray(): array
    {
        $row = [
            'name' => $this->role->name(),
            'permissions' => []
        ];

        foreach ($this->role->permissions() as $permission) {
            $row['permissions'][] = (string)$permission;
        }

        return $row;
    }
}
