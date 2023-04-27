<?php

namespace Domains\AdminPanel\Models\Accounts;

use Doctrine\Common\Collections\Collection;
use Domains\AdminPanel\Events;
use Domains\AdminPanel\Repositories\UserRepositoryContract;
use Domains\AdminPanel\Validation\UserValidator;
use Domains\Common\Exceptions\DomainAuthException;
use Domains\Common\Exceptions\ValidationException;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\Role;

class User extends AggregateRoot
{

    protected string $email;
    protected ?string $firstName;
    protected ?string $lastName;
    private ?string $displayName;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /** @var Collection<UserCompanyAccount> */
    private Collection $userCompanyAccounts;

    /**
     * @param AggregateRootId $id
     * @param string          $email
     * @param string          $firstName
     * @param string          $lastName
     * @param Collection      $userCompanyAccounts
     */
    private function __construct(
        AggregateRootId $id,
        string $email,
        string $firstName,
        string $lastName,
        Collection $userCompanyAccounts
    ) {
        parent::__construct($id);

        $this->userCompanyAccounts = $userCompanyAccounts;

        $this->email = strtolower($email);
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->displayName = sprintf('%s %s', $firstName, $lastName);
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }


    /**
     * Edit user
     * @param UserRepositoryContract    $userRepository
     * @param AggregateRootId           $editedCompanyAccountId
     * @param string|null               $email
     * @param string|null               $firstName
     * @param string|null               $lastName
     * @param string|null               $roleName
     * @throws DomainAuthException
     * @throws ValidationException
     */
    public function edit(
        UserRepositoryContract $userRepository,
        AggregateRootId $editedCompanyAccountId,
        ?string $email,
        ?string $firstName,
        ?string $lastName,
        ?string $roleName
    ): void
    {
        $userCompanyAccount = $this->userCompanyAccount($editedCompanyAccountId);
        if (!$userCompanyAccount) {
            throw DomainAuthException::notMemberOfCompany($editedCompanyAccountId);
        }

        $newRole = null;
        if ($roleName) {
            $newRole = Role::fromName($roleName);
        }

        $validator = new UserValidator($userRepository);
        if (!is_null($firstName)) {
            $validator->firstName($firstName);
        }
        if (!is_null($lastName)) {
            $validator->lastName($lastName);
        }

        if (!is_null($email)) {
            $validator->email($email)->emailUnique($email, $this->uuid());
        }

        if ($validator->exception()->hasErrors()) {
            throw $validator->exception();
        }

        $this->firstName = is_null($firstName) ? $this->firstName : $firstName;
        $this->lastName = is_null($lastName) ? $this->lastName : $lastName;
        $this->displayName = sprintf('%s %s', $this->firstName(), $this->lastName());
        $this->email = is_null($email) ? $this->email : $email;

        $this->updatedAt = new \DateTimeImmutable();
        $this->nextVersion();

        $event = new Events\Accounts\UserEdited($this, $newRole);
        $userCompanyAccount->applyUserEdited($event);

        $userRepository->store($this);
        domainEvent($event);
    }

    /**
     * @param UserRepositoryContract $userRepository
     */
    public function delete(UserRepositoryContract $userRepository): void
    {
        $this->updatedAt = new \DateTimeImmutable();
        $this->nextVersion();

        $event = new Events\Accounts\UserDeleted($this);

        /** @var UserCompanyAccount $userCompanyAccount */
        foreach ($this->userCompanyAccounts() as $userCompanyAccount) {
            $userCompanyAccount->applyUserDeleted($event);
        }

        $userRepository->store($this);
    }

    /**
     * Does user have given role in or without company account as context
     * @param Role                 $role
     * @param AggregateRootId $companyAccountId
     * @return bool
     */
    public function hasRole(Role $role, AggregateRootId $companyAccountId): bool
    {
        /** @var UserCompanyAccount $row */
        foreach ($this->userCompanyAccounts as $row) {
            if (!$row->companyAccount()->uuid()->equals($companyAccountId)) {
                continue;
            }

            if ($row->role()->same($role)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return string
     */
    public function email(): string
    {
        return $this->email;
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
     * @return string|null
     */
    public function displayName(): ?string
    {
        return $this->displayName;
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
     * @return Collection<UserCompanyAccount>
     */
    public function userCompanyAccounts(): Collection
    {
        return $this->userCompanyAccounts;
    }

    /**
     * @param AggregateRootId|null $companyAccountId
     * @return UserCompanyAccount|null
     */
    public function userCompanyAccount(?AggregateRootId $companyAccountId): ?UserCompanyAccount
    {
        $userCompanyAccount = $this->userCompanyAccounts->filter(
            function(UserCompanyAccount $userCompanyAccount) use ($companyAccountId) {
                if (is_null($companyAccountId)) {
                    return true;
                }
                return $userCompanyAccount->companyAccount()->uuid()->equals($companyAccountId);
            })
            ->first();

        if(!$userCompanyAccount) {
            return null;
        }

        return $userCompanyAccount;
    }
}
