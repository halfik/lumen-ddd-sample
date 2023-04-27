<?php

namespace Domains\Accounts\Models\Company;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domains\Accounts\Events;
use Domains\Accounts\Models\Permission\ActionPermissions;
use Domains\Accounts\Models\User\User;
use Domains\Accounts\Models\User\UserId;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Common\Events as CommonEvents;
use Domains\Common\Exceptions\DomainAuthException;
use Domains\Common\Exceptions\DomainException;
use Domains\Common\Exceptions\ValidationException;
use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Paginator;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Common\Models\Permission\Role;
use Domains\Common\Models\WorkflowContract;

class CompanyAccount extends AggregateRoot implements CompanyAccountContract
{
    private const COMPANY_NAME_MAX = 255;

    private string $name;
    private bool $isActive;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /** @var Collection||UserCompanyAccount[]  */
    private Collection $users;
    /** @var Collection||WorkflowContract[]  */
    private Collection $workflows;

    /**
     * Create a new company
     *
     * @param CompanyAccountId $id
     * @param UserContract $owner
     * @throws ValidationException
     * @throws \Exception
     */
    private function __construct(
        CompanyAccountId $id,
        UserContract $owner,
    )
    {
        parent::__construct($id);

        $name = $owner->displayName() . '\'s Company Account';
        if (strlen($name) > self::COMPANY_NAME_MAX) {
            $validationException = new ValidationException();
            $validationException->addError('company_account_name', 'max', [self::COMPANY_NAME_MAX]);
            throw $validationException;
        }

        $this->isActive = true;
        $this->name = $name;
        $this->users = new ArrayCollection();
        $this->workflows = new ArrayCollection();

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        # Don't create UserCompanyAccount automatically in case of system user
        if (!$owner->uuid()->equals(new UserId(UserContract::SYSTEM_ENTITY_ID))) {
            UserCompanyAccount::createNew($this, $owner, Role::companyOwner());
        }
    }

    /**
     * Create company account on user registered event
     *
     * @param Events\UserCreated $event
     * @return static
     * @throws ValidationException
     * @throws \Exception
     */
    public static function fromUserRegistered(Events\UserCreated $event): self
    {
        return new self(
            CompanyAccountId::nextId(),
            $event->user()
        );
    }

    /**
     * Method for the purposes of @see SystemUserBuilderContract only. Use this builder instead!
     *
     * Create dummy company account to perform system actions
     *
     * @return static
     * @throws ValidationException
     * @throws \Exception
     */
    public static function system(): self
    {
        return new self(
            new CompanyAccountId(self::SYSTEM_ENTITY_ID),
            User::system()
        );
    }

    /**
     * @param AggregateRootId $userId
     * @param bool            $onlyActive
     * @return bool
     */
    public function hasMember(AggregateRootId $userId, bool $onlyActive = false): bool
    {
        $member = $this->findMember($userId);
        $hasMember = !is_null($member);
        if ($hasMember && $onlyActive) {
            $hasMember = $member->isActive();
        }

        return $hasMember;
    }

    /**
     * Find company member
     * @param UserId|AggregateRootId $userId
     * @return UserCompanyAccount|null
     */
    public function findMember(UserId|AggregateRootId $userId): ?UserCompanyAccount
    {
        $member = $this->users()->filter(function(UserCompanyAccount $userCompanyAccount) use($userId) {
            return $userCompanyAccount->user()->uuid()->equals(
                $userId
            );
        })->first();
        if (!$member) {
            return null;
        }
        return $member;
    }

    /**
     * Activate company users accounts
     * @param UserRepositoryContract $userRepository
     * @param User                   $requestedBy
     * @param array|string[]         $usersIds     $usersIds
     * @throws DomainAuthException
     * @throws DomainException
     */
    public function activateMembers(
        UserRepositoryContract $userRepository,
        User $requestedBy,
        array $usersIds
    ): void
    {
        foreach ($usersIds as $createUserId) {
            $userId = new UserId($createUserId);
            $editedUserCompanyAccount = $this->findMember($userId);
            if (!$editedUserCompanyAccount) {
                continue;
            }

            /** @var UserCompanyAccount $userCompanyAccount */
            $userCompanyAccount = $requestedBy->userCompanyAccount($editedUserCompanyAccount->companyAccount()->uuid());
            if (!$userCompanyAccount) {
                continue;
            }

            $editedUserCompanyAccount->activate($userCompanyAccount);
            /** @var User $user */
            $user = $editedUserCompanyAccount->user();
            $userRepository->store($user);
        }
    }

    /**
     * Deactivate company users accounts
     * @param UserRepositoryContract $userRepository
     * @param User                   $requestedBy
     * @param array|string[]                  $usersIds     $usersIds
     * @throws DomainAuthException
     * @throws DomainException
     */
    public function deactivateMembers(
        UserRepositoryContract $userRepository,
        User $requestedBy,
        array $usersIds
    ): void
    {
        foreach ($usersIds as $createUserId) {
            try {
                $userId = new UserId($createUserId);
            } catch (\Exception $exception) {
                continue;
            }
            $editedUserCompanyAccount = $this->findMember($userId);
            if (!$editedUserCompanyAccount) {
                continue;
            }

            /** @var UserCompanyAccount $userCompanyAccount */
            $userCompanyAccount = $requestedBy->userCompanyAccount($editedUserCompanyAccount->companyAccount()->uuid());
            if (!$userCompanyAccount) {
                continue;
            }

            $editedUserCompanyAccount->deactivate($userCompanyAccount);
            /** @var User $user */
            $user = $editedUserCompanyAccount->user();
            $userRepository->store($user);
        }
    }


    /**
     * @param UserRepositoryContract $userRepository
     * @param User                   $requestedBy
     * @param string                 $status
     * @param int                    $page
     * @param string|null            $searchPhrase
     * @return Paginator
     * @throws DomainAuthException
     * @throws \Exception
     */
    public function listUsers(
        UserRepositoryContract $userRepository,
        User $requestedBy,
        string $status,
        int $page,
        ?string $searchPhrase
    ): Paginator
    {
        /** @var ActionPerformedByContract $requestedByCompanyUser */
        $requestedByCompanyUser = $requestedBy->userCompanyAccount($this->uuid());
        if(!$requestedByCompanyUser) {
            throw DomainAuthException::notMemberOfCompany($this->uuid());
        }

        self::checkPermission(
            new ActionPermissions\Accounts\ListCompanyUsers(),
            new ActionContext($requestedByCompanyUser, $this)
        );

        $limitedToRoles = $requestedByCompanyUser->role()->visibleRoles();

        return $userRepository->list(
            $this->uuid(),
            new UserCompanyAccountStatus($status),
            $page,
            20,
            $searchPhrase,
            $limitedToRoles
        );
    }

    /**
     * @param Events\UserCompanyAccountCreated $event
     * @throws DomainException
     */
    public function applyUserCompanyAccountCreated(Events\UserCompanyAccountCreated $event): void
    {
        if (!$this->uuid()->equals($event->userCompanyAccount()->companyAccount()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong company account: %s - %s',
                (string)$this->uuid(),
                (string)$event->userCompanyAccount()->companyAccount()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        $this->users->add($event->userCompanyAccount());
    }

    /**
     * @param CommonEvents\WorkflowCreatedContract $event
     * @throws DomainException
     */
    public function applyWorkflowCreated(CommonEvents\WorkflowCreatedContract $event): void
    {
        if (!$this->uuid()->equals($event->workflow()->companyAccount()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong company account: %s - %s',
                (string)$this->uuid(),
                (string)$event->workflow()->companyAccount()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        $this->workflows->add($event->workflow());
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->isActive;
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

    /**
     * @return Collection||UserCompanyAccount[]
     */
    public function users(): Collection
    {
        return $this->users;
    }

    /**
     * @return Collection
     */
    public function workflows(): Collection
    {
        return $this->workflows;
    }

    /**
     * @return WorkflowContract
     */
    public function defaultWorkflow(): WorkflowContract
    {
        return $this->workflows->first();
    }
}
