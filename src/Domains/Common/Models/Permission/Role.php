<?php

namespace Domains\Common\Models\Permission;

use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Accounts\Models\Permission\ActionPermissions\Accounts as AccountPermissions;
use Domains\Common\Models\ValueObject;
use Domains\Sales\Models\Permissions\ActionPermissions as SalesPermissions;

class Role implements ValueObject
{
    // business roles
    private const ROLE_ADMIN = 'admin';
    private const ROLE_COMPANY_OWNER = 'company-owner';

    // system roles
    private const ROLE_SYSTEM = 'system';
    private const ROLE_SYSTEM_ADMIN = 'system-admin';

    private const ALL_ROLES = [
        self::ROLE_ADMIN,
        self::ROLE_COMPANY_OWNER,
        self::ROLE_SYSTEM,
        self::ROLE_SYSTEM_ADMIN
    ];

    // when granted can't be removed
    private const PERMANENT_ROLES = [
        self::ROLE_COMPANY_OWNER,
        self::ROLE_SYSTEM,
        self::ROLE_SYSTEM_ADMIN
    ];

    private string $name;

    /**
     * Lower value - more role can do
     * @var int
     */
    private int $lvl;

    /** @var ActionPermission[]||array */
    private array $permissions;

    /**
     * @param string                   $name
     * @param array|ActionPermission[] $permissions
     * @param int                      $lvl
     */
    public function __construct(string $name, array $permissions, int $lvl = 1)
    {
        if (!in_array($name, self::ALL_ROLES)) {
            throw new \InvalidArgumentException('Unknown role');
        }

        $this->name = $name;
        $this->permissions = $permissions;
        $this->lvl = $lvl;
    }

    /**
     * Create role based on given name
     * @param string $name
     * @return static
     * @throws \Exception
     */
    public static function fromName(string $name): self
    {
        return match ($name) {
            self::ROLE_COMPANY_OWNER => self::companyOwner(),
            self::ROLE_ADMIN => self::admin(),
            self::ROLE_SYSTEM_ADMIN => self::systemAdmin(),
        };
    }

    /**
     * @return static
     */
    public static function system(): self
    {
        return new self(self::ROLE_SYSTEM, [], 1);
    }

    /**
     * @return static
     */
    public static function systemAdmin(): self
    {
        return new self(self::ROLE_SYSTEM_ADMIN, [], 10);
    }

    /**
     * Company admin role
     * @return static
     * @throws \Exception
     */
    public static function admin(): self
    {
        return new self(
            self::ROLE_ADMIN,
            [
                PermissionsContract::PERMISSIONS_GROUP_USER => [
                    new AccountPermissions\ListCompanyUsers(),
                    new AccountPermissions\ViewCompanyUserDetails(),
                    new AccountPermissions\LoginAsUser(),
                    new AccountPermissions\EditUserAccount(),
                    new AccountPermissions\ChangeUserRole(),
                    new AccountPermissions\ActivateUserCompanyAccount(),
                    new AccountPermissions\DeactivateUserCompanyAccount(),
                    AccountPermissions\ChangeUserPassword::onlyOwnAccount(),
                ],

                PermissionsContract::PERMISSIONS_GROUP_LEAD => [
                    new SalesPermissions\CreateLead(),
                    new SalesPermissions\EditLead(),
                    new SalesPermissions\CloseLead(),
                    new SalesPermissions\DeleteLead(),
                    new SalesPermissions\ViewLead(),
                ],


                PermissionsContract::PERMISSIONS_GROUP_WORKFLOW => [
                    new SalesPermissions\ViewWorkflow(),
                    new SalesPermissions\CreateStage(),
                    new SalesPermissions\EditStage(),
                ],
            ],
            20
        );
    }

    /**
     * Company owner role
     * @return static
     * @throws \Exception
     */
    public static function companyOwner(): self
    {
        return new self(
            self::ROLE_COMPANY_OWNER,
            [
                PermissionsContract::PERMISSIONS_GROUP_USER => [
                    new AccountPermissions\ListCompanyUsers(),
                    new AccountPermissions\ViewCompanyUserDetails(),
                    new AccountPermissions\LoginAsUser(),
                    new AccountPermissions\EditUserAccount(),
                    new AccountPermissions\ChangeUserRole(),
                    new AccountPermissions\ActivateUserCompanyAccount(),
                    new AccountPermissions\DeactivateUserCompanyAccount(),
                    AccountPermissions\ChangeUserPassword::onlyOwnAccount(),
                    new AccountPermissions\SetQuota(null),
                ],


                PermissionsContract::PERMISSIONS_GROUP_LEAD => [
                    new SalesPermissions\CreateLead(),
                    new SalesPermissions\EditLead(),
                    new SalesPermissions\CloseLead(),
                    new SalesPermissions\DeleteLead(),
                    new SalesPermissions\ViewLead(),
                ],


                PermissionsContract::PERMISSIONS_GROUP_WORKFLOW => [
                    new SalesPermissions\ViewWorkflow(),
                    new SalesPermissions\CreateStage(),
                    new SalesPermissions\EditStage(),
                ],
            ],
            20
        );
    }

    /**
     * @return bool
     */
    public function isPermanent(): bool
    {
       return in_array($this->name(), self::PERMANENT_ROLES);
    }

    /**
     * @param Role $oRole
     * @return bool
     */
    public function same(Role $oRole): bool
    {
        return $this->name() === $oRole->name();
    }

    /**
     * Check if current role has power over other role
     * @param Role $oRole
     * @return bool
     */
    public function hasPowerOver(Role $oRole): bool
    {
        return $this->lvl() <= $oRole->lvl();
    }

    /**
     * Check if role does have given permission
     *
     * @param ActionPermission $askedAction
     * @param ActionContext $actionContext
     * @return bool
     */
    public function hasPermission(ActionPermission $askedAction, ActionContext $actionContext): bool
    {
        if ($this->same(Role::system()) || $this->same(Role::systemAdmin())) {
            return true;
        }

        /**
         * We check if action is same lvl or above user we perform it on
         * Exception are: we  perform it on our self or its view type action
         */
        if ($actionContext->performedOn() instanceof UserCompanyAccount &&
            !$actionContext->isPerformedOnHimself() &&
            !$askedAction->isViewOnlyType() &&
            !$this->hasPowerOver($actionContext->performedOn()->role())
        ) {
            return false;
        }

        /**
         * Role need permission that was asked
         * And all restrictions have to pass vs action context
         */
        $result = false;
        foreach ($this->permissions() as $actionPermission) {
            if ($actionPermission->same($askedAction)) {
                foreach ($actionPermission->restrictions() as $actionRestriction) {
                    if (!$actionRestriction->pass($actionContext)) {
                        return false;
                    }
                }
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Get list of visible roles eg. for user listing
     * @return Role[]
     * @throws \Exception
     */
    public function visibleRoles(): array
    {
        return match ($this->name()) {
            self::ROLE_COMPANY_OWNER, self::ROLE_ADMIN => [Role::admin()],
            default => [],
        };
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function lvl(): int
    {
        return $this->lvl;
    }

    /**
     * @return ActionPermission[]
     */
    public function permissions(bool $flat = true): array
    {
        if ($flat) {
            $flatt = [];
            foreach(array_values($this->permissions) as $row) {
                $flatt = array_merge($flatt, $row);
            }
            return $flatt;
        }
        return $this->permissions;
    }

    /**
     * @param string $groupName
     * @return ActionPermission[]
     */
    public function permissionsGroup(string $groupName): array
    {
        return $this->permissions[$groupName] ?? [];
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name(),
            'level' => $this->lvl(),
            'permissions' => array_map( function (ActionPermission $permission){
                return $permission->toArray();
            }, $this->permissions()),
        ];
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public static function fromRawData(array $data): static
    {
        return self::fromName($data['name']);
    }

    /**
     * @inheritDoc
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_HEX_TAG);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->name();
    }

}
