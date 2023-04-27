<?php

namespace Domains\Accounts\Models\Company;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domains\Accounts\Events;
use Domains\Accounts\Models\Permission\ActionPermissions;
use Domains\Accounts\Models\User\User;
use Domains\Common\Exceptions\DomainAuthException;
use Domains\Common\Exceptions\DomainException;
use Domains\Common\Exceptions\ValidationException;
use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\Account\UserCompanyAccountContract;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Common\Models\Permission\Role;


class UserCompanyAccount extends AggregateRoot implements
    ActionPerformedByContract,
    UserCompanyAccountContract
{
    protected CompanyAccountContract $companyAccount;
    protected User $user;
    protected Role $role;

    protected UserCompanyAccountStatus $status;

    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param UserCompanyAccountId          $id
     * @param CompanyAccountContract        $companyAccount
     * @param User                          $user
     * @param Role                          $role
     * @param UserCompanyAccountStatus|null $status
     */
    private function __construct(
        UserCompanyAccountId $id,
        CompanyAccountContract $companyAccount,
        User $user,
        Role $role,
        UserCompanyAccountStatus $status = null
    )
    {
        parent::__construct($id);

        if (!$status) {
            $status = UserCompanyAccountStatus::pending();
        }

        $this->user = $user;
        $this->companyAccount = $companyAccount;
        $this->role = $role;
        $this->status = $status;

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @param CompanyAccountContract $companyAccount
     * @param User $user
     * @param Role $role
     * @param UserCompanyAccountStatus|null $status
     * @param ActionPerformedByContract|null $performedBy
     * @return static
     * @throws DomainException
     * @throws \Exception
     */
    public static function createNew(
        CompanyAccountContract $companyAccount,
        UserContract $user,
        Role $role,
        UserCompanyAccountStatus $status = null,
        ActionPerformedByContract $performedBy = null,
    ): self {
        $model = new self(
            UserCompanyAccountId::nextId(),
            $companyAccount,
            $user,
            $role,
            $status
        );

        $event = new Events\UserCompanyAccountCreated($performedBy ?? $model, $model);
        $user->applyUserCompanyAccountCreated($event);
        $companyAccount->applyUserCompanyAccountCreated($event);
        domainEvent($event);

        return $model;
    }

    /**
     * Method for the purposes of @see SystemUserBuilderContract only. Use this builder instead!
     *
     * Create dummy user company account to perform system actions
     *
     * @return UserCompanyAccountContract|ActionPerformedByContract
     * @throws DomainException
     * @throws ValidationException
     * @throws \Exception
     */
    public static function system(): UserCompanyAccountContract|ActionPerformedByContract
    {
        $model = new self(
            new UserCompanyAccountId(self::SYSTEM_ENTITY_ID),
            CompanyAccount::system(),
            User::system(),
            Role::system(),
            null,
            UserCompanyAccountStatus::active()
        );

        $event = new Events\UserCompanyAccountCreated($model, $model);
        $model->user()->applyUserCompanyAccountCreated($event);
        $model->companyAccount()->applyUserCompanyAccountCreated($event);

        return $model;
    }

    /**
     * Activate user company account
     * @param UserCompanyAccount $requestedBy
     * @throws DomainException
     */
    public function activate(UserCompanyAccount $requestedBy):  void
    {
        if (
            !$requestedBy->uuid()->equals($this->uuid()) &&
            !$this->status()->same(UserCompanyAccountStatus::inactive())
        ) {
            throw DomainException::accountStatusTransitionNowAllowed();
        }

        if (!$this->companyAccount()->isActive()) {
            throw DomainException::companyAccountsInactive();
        }

        self::checkPermission(
            new ActionPermissions\Accounts\ActivateUserCompanyAccount(),
            new ActionContext($requestedBy, $this)
        );

        $oldValues = $this->toArray();
        $this->status = UserCompanyAccountStatus::active();
        $this->updatedAt = new \DateTimeImmutable();
        $this->nextVersion();

        $event = new Events\UserCompanyAccountActivated($requestedBy, $this, $oldValues);
        $this->user->applyUserCompanyAccountActivated($event);

        domainEvent($event);
    }

    /**
     * Deactivate account
     * @param UserCompanyAccount $requestedBy
     * @throws DomainAuthException
     * @throws DomainException
     */
    public function deactivate(UserCompanyAccount $requestedBy): void
    {
        self::checkPermission(
            new ActionPermissions\Accounts\DeactivateUserCompanyAccount(),
            new ActionContext($requestedBy, $this)
        );

        $newStatus = UserCompanyAccountStatus::inactive();
        if (!$this->status()->isTransitionAllowed($newStatus)) {
            throw DomainException::accountStatusTransitionNowAllowed();
        }

        $oldValues = $this->toArray();
        $this->status = $newStatus;
        $this->updatedAt = new \DateTimeImmutable();
        $this->nextVersion();

        $event = new Events\UserCompanyAccountDeactivated($requestedBy, $this, $oldValues);
        domainEvent($event);
    }

    /**
     * @param Events\UserEdited $event
     */
    public function applyUserEdited(Events\UserEdited $event): void
    {
        // some roles are protected can't be changed
        if (!$this->role->isPermanent() && !$event->newRole()->isPermanent()) {
            $this->role = $event->newRole();
        }

        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * @return CompanyAccountContract
     */
    public function companyAccount(): CompanyAccountContract
    {
        return $this->companyAccount;
    }

    /**
     * @inheritDoc
     */
    public function isMemberOf(CompanyAccountContract $companyAccount): bool
    {
        if ($this->role->same(Role::system())) {
            return true;
        }

        return $companyAccount->uuid()->equals($this->companyAccount()->uuid());
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->status()->same(UserCompanyAccountStatus::active());
    }

    /**
     * @return UserContract
     */
    public function user(): UserContract
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
     * @return UserCompanyAccountStatus
     */
    public function status(): UserCompanyAccountStatus
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
