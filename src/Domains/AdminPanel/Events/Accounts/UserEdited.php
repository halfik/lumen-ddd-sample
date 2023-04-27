<?php

namespace Domains\AdminPanel\Events\Accounts;

use Domains\Common\Events\Accounts\UserAccountEditedContract;
use Domains\Common\Events\DomainEvent;
use Domains\Common\Models\Permission\Role;
use Domains\AdminPanel\Events\AdminEventContract;
use Domains\AdminPanel\Models\Accounts\User;

class UserEdited extends DomainEvent implements
    AdminEventContract,
    UserAccountEditedContract
{
    private const VERSION = 1;

    /**
     * @param User      $performedOn
     * @param Role|null $newRole
     */
    public function __construct(private User $performedOn, private ?Role $newRole) {
    }

    /**
     * @return User
     */
    public function performedOn(): User
    {
        return $this->performedOn;
    }

    /**
     * @return Role|null
     */
    public function newRole(): ?Role
    {
        return $this->newRole;
    }


    /**
     * @return string
     */
    public function domain(): string
    {
        return self::DOMAIN_ADMIN_ACCOUNTS;
    }

    /**
     * @return string
     */
    public function model(): string
    {
        return self::MODEL_USER;
    }

    /**
     * @return string
     */
    public function action(): string
    {
        return self::ACTION_EDITED;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return self::VERSION;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['user_id'] = (string)$this->performedOn()->uuid();

        return $data;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return strtolower(sprintf('%s.%s.%s', $this->domain(), $this->model(), $this->action()));
    }
}
