<?php

namespace Domains\Accounts\Repositories;

use Domains\Accounts\Models\Company\CompanyAccountId;
use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Accounts\Models\User\UserId;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Paginator;
use Domains\Common\Repositories\LocalizedRepositoryContract;

interface UserRepositoryContract extends LocalizedRepositoryContract
{
    /**
     * Find user by given email
     * @param string               $email
     * @param AggregateRootId|null $editedUserId
     * @return UserContract|null
     */
    public function findOneByEmail(string $email, ?AggregateRootId $editedUserId): ?UserContract;

    /**
     * Find user by id
     * @param UserId $userId
     * @return UserContract|null
     */
    public function findById(UserId $userId): ?UserContract;

    /**
     * Find users by ids
     * @param array $userIds
     * @return array
     */
    public function findByIds(array $userIds): array;

    /**
     * List users of given company account
     * @param CompanyAccountId         $companyAccountId
     * @param UserCompanyAccountStatus $status
     * @param int                      $page
     * @param int                      $itemsPerPage
     * @param string|null               $searchPhrase
     * @param array|null                $limitedToRoles
     * @return Paginator
     */
    public function list(
        CompanyAccountId $companyAccountId,
        UserCompanyAccountStatus $status,
        int $page,
        int $itemsPerPage,
        ?string $searchPhrase,
        ?array $limitedToRoles
    ): Paginator;

    /**
     * Create or update given user
     * @param UserContract $model
     * @return $this
     */
    public function store(UserContract $model): self;

    /**
     * Flush changes to data storage
     * @return $this
     */
    public function flush(): self;
}
