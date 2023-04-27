<?php

namespace Domains\Accounts\Models\User;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domains\Accounts\Emails\UserRegisteredMail;
use Domains\Accounts\Events;
use Domains\Accounts\Models\Company\CompanyAccount;
use Domains\Accounts\Models\Company\CompanyAccountId;
use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Accounts\Models\Permission\ActionPermissions;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Accounts\Validation\UserValidator;
use Domains\Common\Exceptions\DomainAuthException;
use Domains\Common\Exceptions\DomainException;
use Domains\Common\Exceptions\ValidationException;
use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\Account\UserCompanyAccountContract;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Common\Models\Permission\ActionPerformedOnContract;
use Domains\Common\Models\Permission\ActionPermission;
use Domains\Common\Models\Permission\Role;
use Domains\Common\Notification\Mailer;
use Domains\Sales\Repositories\WorkflowRepositoryContract;

class User extends AggregateRoot implements UserContract
{
    protected string $email;
    protected Password $password;

    protected ?\DateTimeImmutable $emailVerifiedAt;

    protected ?string $title;

    protected ?string $firstName;
    protected ?string $lastName;
    protected ?string $middleName;
    private ?string $displayName;

    protected ?string $countryCode;
    protected ?string $phoneNumber;

    protected \DateTimeImmutable $createdAt;
    protected \DateTimeImmutable $updatedAt;

    /** @var ?Collection */
    private ?Collection $userCompanyAccounts;

    /**
     * @param UserId      $id
     * @param string      $email
     * @param string      $password
     * @param string      $firstName
     * @param string      $lastName
     * @param string|null $title
     */
    private function __construct(
        UserId $id,
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        ?string $title,
    ) {
        parent::__construct($id);

        $this->userCompanyAccounts = new ArrayCollection();

        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->email = strtolower($email);
        $this->emailVerifiedAt = null;
        $this->password = Password::hash($password);

        $this->title = $title;

        $this->middleName = null;
        $this->displayName = sprintf('%s %s', $firstName, $lastName);

        $this->countryCode = null;
        $this->phoneNumber = null;

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Register user
     *
     * @param UserRepositoryContract     $userRepository
     * @param WorkflowRepositoryContract $workflowRepository
     * @param Mailer                     $mailer
     * @param string                     $email
     * @param string                     $password
     * @param string                     $confirmPassword
     * @param string                     $firstName
     * @param string                     $lastName
     * @return static
     * @throws ValidationException
     */
    public static function register(
        UserRepositoryContract $userRepository,
        WorkflowRepositoryContract $workflowRepository,
        Mailer $mailer,
        string $email,
        string $password,
        string $confirmPassword,
        string $firstName,
        string $lastName,
    ): self {
        $validator = new UserValidator($userRepository);
        $validator->firstName($firstName)
            ->lastName($lastName)
            ->password($password)
            ->passwordConfirmation($password, $confirmPassword)
            ->email($email)
            ->emailUnique($email);

        if ($validator->exception()->hasErrors()) {
            throw $validator->exception();
        }

        $user = new self(
            UserId::nextId(),
            $email,
            $password,
            $firstName,
            $lastName,
            null
        );

        $event = new Events\UserCreated($user, $user);
        $companyAccount = CompanyAccount::fromUserRegistered($event);
        $workflow = $workflowRepository->createDefault($companyAccount);

        $userRepository->store($user);
        $workflowRepository->store($workflow);

        $mail = new UserRegisteredMail($user);
        $mailer->send($mail);

        domainEvent($event);

        return $user;
    }

    /**
     * Method for the purposes of @see SystemUserBuilderContract only. Use this builder instead!
     *
     * Create dummy user to perform system actions
     *
     * @return static
     * @throws \Exception
     */
    public static function system(): self {
        return new self(
            new UserId(self::SYSTEM_ENTITY_ID),
            env('SYSTEM_USER_EMAIL', 'system@dummy.co'),
            env('SYSTEM_USER_PASSWORD', '123^2TpkmPgSy_F#'),
            env('SYSTEM_USER_FIRST_NAME', 'System'),
            env('SYSTEM_USER_LAST_NAME', 'Dummy'),
            null
        );
    }

    /**
     * Check given permission in given company
     *
     * @param CompanyAccount   $companyAccount
     * @param ActionPermission $permissionToCheck
     * @param ActionContext    $actionContext
     * @return bool
     */
    public function hasPermission(
        CompanyAccount $companyAccount,
        ActionPermission $permissionToCheck,
        ActionContext $actionContext
    ): bool
    {
        $userCompanyAccount = $companyAccount->findMember($this->uuid());
        if (!$userCompanyAccount) {
            return false;
        }

        return $userCompanyAccount->role()->hasPermission($permissionToCheck, $actionContext);
    }

    /**
     * Verify is password does match user
     * @param string $plainPassword
     * @return bool
     */
    public function verifyPassword(string $plainPassword): bool
    {
        return $this->password()->verify($plainPassword);
    }

    /**
     * Change user password
     * @param UserRepositoryContract    $userRepository
     * @param string                    $newPassword
     * @param string                    $newPasswordConfirmation
     * @param string                    $currentPassword
     * @param ActionPerformedByContract $requestedBy
     * @throws DomainAuthException
     * @throws ValidationException
     */
    public function changePassword(
        UserRepositoryContract $userRepository,
        string $newPassword,
        string $newPasswordConfirmation,
        string $currentPassword,
        ActionPerformedByContract $requestedBy
    ): void
    {
        /** @var CompanyAccountId $companyAccountId */
        $companyAccountId = $requestedBy->companyAccount()->uuid();
        /** @var ActionPerformedOnContract $userCompanyAccount */
        $userCompanyAccount = $this->userCompanyAccount($companyAccountId);
        if(!$userCompanyAccount) {
            throw DomainAuthException::notMemberOfCompany($companyAccountId);
        }

        self::checkPermission(
            new ActionPermissions\Accounts\ChangeUserPassword(),
            new ActionContext($requestedBy, $userCompanyAccount)
        );

        $validator = new UserValidator($userRepository);
        $validator->password($newPassword)
            ->passwordConfirmation($newPassword, $newPasswordConfirmation)
            ->passwordVerify($this->password(), $currentPassword);

        if ($validator->exception()->hasErrors()) {
            throw $validator->exception();
        }

        $this->password = Password::hash($newPassword);
        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();

        $userRepository->store($this);
    }

    /**
     * Edit user
     *
     * @param UserRepositoryContract $userRepository
     * @param User                   $editedBy
     * @param CompanyAccountId       $editedCompanyAccountId
     * @param string|null            $firstName
     * @param string|null            $lastName
     * @param string|null            $title
     * @param string|null            $phoneNumber
     * @param string|null            $countryCode
     * @param string|null            $roleName
     * @throws DomainAuthException
     * @throws ValidationException
     */
    public function edit(
        UserRepositoryContract $userRepository,
        User $editedBy,
        CompanyAccountId $editedCompanyAccountId,
        ?string $firstName,
        ?string $lastName,
        ?string $title,
        ?string $phoneNumber,
        ?string $countryCode,
        ?string $roleName
    ): void {
        /** @var ActionPerformedByContract $editedByUserCompany */
        $editedByUserCompany = $editedBy->userCompanyAccount($editedCompanyAccountId);
        if (!$editedByUserCompany) {
            throw DomainAuthException::notMemberOfCompany($editedCompanyAccountId);
        }

        /** @var ActionPerformedOnContract $userCompanyAccount */
        $userCompanyAccount = $this->userCompanyAccount($editedCompanyAccountId);
        if (!$userCompanyAccount) {
            throw DomainAuthException::notMemberOfCompany($editedCompanyAccountId);
        }

        self::checkPermission(
            new ActionPermissions\Accounts\EditUserAccount(),
            new ActionContext($editedByUserCompany, $userCompanyAccount)
        );

        $newRole = null;
        if ($roleName) {
            $newRole = Role::fromName($roleName);
            self::checkPermission(
                new ActionPermissions\Accounts\ChangeUserRole(),
                new ActionContext($editedByUserCompany, $userCompanyAccount, [$newRole])
            );
        }

        $validator = new UserValidator($userRepository);

        if (!is_null($firstName)) {
            $validator->firstName($firstName);
        }
        if (!is_null($lastName)) {
            $validator->lastName($lastName);
        }
        if (!is_null($countryCode)) {
            $validator->country($countryCode);
        }
        if (!is_null($phoneNumber)) {
            $validator->phone($phoneNumber);
        }
        if (!is_null($title)) {
            $validator->title($title);
        }

        if ($validator->exception()->hasErrors()) {
            throw $validator->exception();
        }

        $this->firstName = is_null($firstName) ? $this->firstName : $firstName;
        $this->lastName = is_null($lastName) ? $this->lastName : $lastName;
        $this->title = is_null($title) ? $this->title : $title;
        $this->displayName = sprintf('%s %s', $this->firstName(), $this->lastName());
        $this->phoneNumber = is_null($phoneNumber) ? $this->phoneNumber : $phoneNumber;
        $this->countryCode = is_null($countryCode) ? $this->countryCode : $countryCode;

        $event = new Events\UserEdited($editedByUserCompany, $this, [], $newRole,);
        $userCompanyAccount->applyUserEdited($event);

        $this->updatedAt = new \DateTimeImmutable();
        $this->nextVersion();

        $userRepository->store($this);
        domainEvent($event);
    }


    /**
     * @param Events\UserCompanyAccountCreated $event
     */
    public function applyUserCompanyAccountCreated(Events\UserCompanyAccountCreated $event): void
    {
        $this->userCompanyAccounts->add($event->userCompanyAccount());
    }

    /**
     * @param Events\UserCompanyAccountActivated $event
     * @throws DomainException
     */
    public function applyUserCompanyAccountActivated(Events\UserCompanyAccountActivated $event): void
    {
        /** @var CompanyAccountId $companyAccountId */
        $companyAccountId = $event->companyAccountId();
        /** @var ActionPerformedByContract $userCompanyAccount */
        $userCompanyAccount = $this->userCompanyAccount($companyAccountId);
        if (!$userCompanyAccount) {
            throw DomainException::notFound(CompanyAccount::class, 'id', (string)$companyAccountId);
        }

        if (!$this->emailVerifiedAt) {
            $this->emailVerifiedAt = new \DateTimeImmutable();
        }
    }

    /**
     * @param UserCompanyAccountStatus|null $status
     * @return Collection|null
     */
    public function userCompanyAccounts(UserCompanyAccountStatus $status = null): ?Collection
    {
        $result = $this->userCompanyAccounts;
        if ($status) {
            $result = $this->userCompanyAccounts->filter(function(UserCompanyAccount $userCompanyAccount) use ($status){
                return $userCompanyAccount->status()->same($status);
            });
        }
        return $result;
    }

    /**
     * @param AggregateRootId|null $companyAccountId
     * @return bool
     */
    public function isActive(AggregateRootId $companyAccountId = null): bool
    {
        if($companyAccountId) {
            /** @var UserCompanyAccount|null $userCompanyAccount */
            $userCompanyAccount = $this->userCompanyAccount($companyAccountId);
            return !is_null($userCompanyAccount) && $userCompanyAccount->isActive();
        }
        return $this->userCompanyAccounts(UserCompanyAccountStatus::active())->count() > 0;
    }

    /**
     * @inheritDoc
     * Get user company account that has role with highest rank
     */
    public function userCompanyAccount(?AggregateRootId $companyAccountId): ?UserCompanyAccountContract
    {
        /** @var UserCompanyAccount|null $userCompanyAccount */
        $userCompanyAccount = null;
        /** @var UserCompanyAccount $row */
        foreach ($this->userCompanyAccounts as $row) {
            if (!is_null($companyAccountId) &&  !$row->companyAccount()->uuid()->equals($companyAccountId)) {
                continue;
            }

            if (is_null($userCompanyAccount) || $row->role()->hasPowerOver($userCompanyAccount->role())) {
                $userCompanyAccount = $row;
            }
        }

        if(!$userCompanyAccount) {
            return null;
        }

        return $userCompanyAccount;
    }

    /**
     * Does user have given role in or without company account as context
     * @param Role                 $role
     * @param AggregateRootId|null $companyAccountId
     * @return bool
     */
    public function hasRole(Role $role, ?AggregateRootId $companyAccountId): bool
    {
        /** @var UserCompanyAccount $row */
        foreach ($this->userCompanyAccounts as $row) {
            if (!is_null($companyAccountId) && !$row->companyAccount()->uuid()->equals($companyAccountId)) {
                continue;
            }

            if ($row->role()->same($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param CompanyAccountContract $companyAccount
     * @return bool
     */
    public function isMemberOf(CompanyAccountContract $companyAccount): bool
    {
        return $this->userCompanyAccount($companyAccount->uuid()) !== null;
    }

    /**
     * @return string
     */
    public function email(): string
    {
        return $this->email;
    }

    /**
     * @return Password
     */
    public function password(): Password
    {
        return $this->password;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function emailVerifiedAt(): ?\DateTimeImmutable
    {
        return $this->emailVerifiedAt;
    }

    /**
     * @return string|null
     */
    public function title(): ?string
    {
        return $this->title;
    }

    /**
     * @return string|null
     */
    public function firstName(): ?string
    {
        return $this->firstName;
    }

    /**
     * @return string|null
     */
    public function lastName(): ?string
    {
        return $this->lastName;
    }

    /**
     * @return string
     */
    public function displayName(): string
    {
        return $this->displayName;
    }

    /**
     * @return string|null
     */
    public function countryCode(): ?string
    {
        return $this->countryCode;
    }

    /**
     * @return string|null
     */
    public function phoneNumber(): ?string
    {
        return $this->phoneNumber;
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
