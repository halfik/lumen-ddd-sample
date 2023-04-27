<?php

namespace Domains\AdminPanel\Repositories;

use Domains\AdminPanel\Models\Accounts\CompanyAccount;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Paginator;

interface CompanyAccountRepositoryContract
{
    /**
     * Find company account by id
     *
     * @param AggregateRootId $userId
     * @return CompanyAccount|null
     */
    public function findById(AggregateRootId $companyAccountId): ?CompanyAccount;

    /**
     * @param int         $page
     * @param int         $perPage
     * @param string|null $searchPhrase
     * @return Paginator
     */
    public function list(
        int $page,
        int $perPage,
        ?string $searchPhrase
    ): Paginator;

    /**
     * Create or update given company account
     * @param CompanyAccount $model
     * @return $this
     */
    public function store(CompanyAccount $model): self;

    /**
     * Flush changes to data storage
     * @return $this
     */
    public function flush(): self;
}
