<?php

namespace Domains\Common\Models\Account;

use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\Role;

interface UserContract
{
    public const SYSTEM_ENTITY_ID = '2942daaa-e34d-4089-acd5-94d45947298b';

    /**
     * @return AggregateRootId
     */
    public function uuid(): AggregateRootId;

    /**
     * @param AggregateRootId|null $companyAccountId
     * @return UserCompanyAccountContract|null
     */
    public function userCompanyAccount(?AggregateRootId $companyAccountId): ?UserCompanyAccountContract;

    /**
     * @param Role                 $role
     * @param AggregateRootId|null $companyAccountId
     * @return bool
     */
    public function hasRole(Role $role, ?AggregateRootId $companyAccountId): bool;

    /**
     * @param CompanyAccountContract $companyAccount
     * @return bool
     */
    public function isMemberOf(CompanyAccountContract $companyAccount): bool;

    /**
     * @param AggregateRootId|null $companyAccountId
     * @return bool
     */
    public function isActive(AggregateRootId $companyAccountId = null): bool;

    /**
     * @return string
     */
    public function email(): string;

    /**
     * @return string|null
     */
    public function title(): ?string;


    /**
     * @return string|null
     */
    public function firstName(): ?string;

    /**
     * @return string|null
     */
    public function lastName(): ?string;

    /**
     * @return string
     */
    public function displayName(): string;

    /**
     * @return string|null
     */
    public function countryCode(): ?string;

    /**
     * @return string|null
     */
    public function phoneNumber(): ?string;

    /**
     * @param AggregateRoot $aggregateRoot
     * @return bool
     */
    public function same(AggregateRoot $aggregateRoot): bool;
}
