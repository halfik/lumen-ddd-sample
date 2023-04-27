<?php

namespace Domains\AdminPanel\Models\Accounts;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domains\AdminPanel\Exceptions\AdminPanelDomainException;
use Domains\Common\Exceptions\DomainException;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;
use Domains\AdminPanel\Events;
use Domains\AdminPanel\Repositories\CompanyAccountRepositoryContract;
use Domains\AdminPanel\Models\Sales\Workflow;
use Domains\Common\Models\Permission\Role;

class CompanyAccount extends AggregateRoot
{
    private string $name;
    private bool $isActive;
    /** @var Collection<UserCompanyAccount> */
    private Collection $users;
    /** @var Collection<Workflow> */
    private Collection $workflows;
    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    /**
     * @param AggregateRootId $id
     * @param string $name
     * @param bool $isActive
     */
    private function __construct(AggregateRootId $id, string $name, bool $isActive)
    {
        parent::__construct($id);

        $this->isActive = $isActive;
        $this->name = $name;
        $this->users = new ArrayCollection();
        $this->workflows = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
    }

    /**
     * Block company account
     * @param CompanyAccountRepositoryContract $companyAccountRepository
     */
    public function block(CompanyAccountRepositoryContract $companyAccountRepository): void
    {
        if (!$this->isActive) {
            return;
        }

        $this->isActive = false;
        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();

        $event = new Events\Accounts\CompanyAccountBlocked($this);
        /** @var UserCompanyAccount $userCompanyAccount */
        foreach ($this->users as $userCompanyAccount) {
            $userCompanyAccount->applyCompanyAccountBlocked($event);
        }

        $companyAccountRepository->store($this);
    }

    /**
     * Unblock company account
     * @param CompanyAccountRepositoryContract $companyAccountRepository
     */
    public function unblock(CompanyAccountRepositoryContract $companyAccountRepository): void
    {
        if ($this->isActive) {
            return;
        }

        $this->isActive = true;
        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();

        $event = new Events\Accounts\CompanyAccountUnblocked($this);
        /** @var UserCompanyAccount $userCompanyAccount */
        foreach ($this->users as $userCompanyAccount) {
            $userCompanyAccount->applyCompanyAccountUnblocked($event);
        }

        $companyAccountRepository->store($this);
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
     * @return Collection<UserCompanyAccount>
     */
    public function users(): Collection
    {
        return $this->users;
    }

    /**
     * @return UserCompanyAccount
     * @throws AdminPanelDomainException
     * @throws DomainException
     */
    public function owner(): UserCompanyAccount
    {
        $owner = $this->users->filter(static function(UserCompanyAccount $userCompanyAccount) {
            return $userCompanyAccount->role()->same(Role::companyOwner());
        })->first();
        if ($owner) {
            return $owner;
        }
        
        throw AdminPanelDomainException::notFoundWithin('User', 'Company');
    }

    /**
     * @return Collection<Workflow>
     */
    public function workflows(): Collection
    {
        return $this->workflows;
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
