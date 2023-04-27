<?php

namespace Domains\AdminPanel\Models\Accounts;

use Domains\AdminPanel\Events;
use Domains\Common\Models\Account\UserCompanyAccountStatusContract;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\Role;

class UserCompanyAccount extends AggregateRoot
{
    protected CompanyAccount $companyAccount;
    protected User $user;
    protected Role $role;
    protected string $status;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param AggregateRootId $id
     * @param User            $user
     * @param Role            $role
     * @param string          $status
     */
    private function __construct(AggregateRootId $id, User $user, Role $role, string $status)
    {
        parent::__construct($id);

        $this->user = $user;
        $this->role = $role;
        $this->status = $status;

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @param Events\Accounts\UserEdited $event
     */
    public function applyUserEdited(Events\Accounts\UserEdited $event): void
    {
        // some roles are protected can't be changed
        if (!$this->role->isPermanent() && !$event->newRole()->isPermanent()) {
            $this->role = $event->newRole();
        }

        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @param Events\Accounts\CompanyAccountBlocked $event
     */
    public function applyCompanyAccountBlocked(Events\Accounts\CompanyAccountBlocked $event): void
    {
        if ($this->status !== UserCompanyAccountStatusContract::STATUS_ACTIVE) {
            return;
        }
        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = UserCompanyAccountStatusContract::STATUS_BLOCKED;

        $event = new Events\Accounts\UserCompanyAccountBlocked(
            $this->user()->uuid(),
            $this->companyAccount()->uuid()
        );
        domainEvent($event);
    }

    /**
     * @param Events\Accounts\CompanyAccountUnblocked $event
     */
    public function applyCompanyAccountUnblocked(Events\Accounts\CompanyAccountUnblocked $event): void
    {
        if ($this->status !== UserCompanyAccountStatusContract::STATUS_BLOCKED) {
            return;
        }

        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = UserCompanyAccountStatusContract::STATUS_ACTIVE;
    }

    /**
     * @param Events\Accounts\UserDeleted $event
     */
    public function applyUserDeleted(Events\Accounts\UserDeleted $event): void
    {
        if ($this->status !== UserCompanyAccountStatusContract::STATUS_ACTIVE) {
            return;
        }
        // sys-admin user can't be deleted
        if ($this->user()->hasRole(Role::systemAdmin(), $this->companyAccount()->uuid())) {
            return;
        }

        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();
        $this->status = UserCompanyAccountStatusContract::STATUS_BLOCKED;

        $event = new Events\Accounts\UserCompanyAccountBlocked(
            $this->user()->uuid(),
            $this->companyAccount()->uuid()
        );
        domainEvent($event);
    }


    /**
     * @return CompanyAccount
     */
    public function companyAccount(): CompanyAccount
    {
        return $this->companyAccount;
    }

    /**
     * @return User
     */
    public function user(): User
    {
        return $this->user;
    }

    /**
     * @return Role
     */
    public function role(): Role
    {
        return $this->role;
    }

    /**
     * @return string
     */
    public function status(): string
    {
        return $this->status;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }
}
