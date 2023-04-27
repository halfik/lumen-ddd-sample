<?php

namespace Domains\Accounts\Repositories;

use Domains\Accounts\Models\Company\CompanyAccount;

interface CompanyAccountRepositoryContract
{
    /**
     * Store company account
     * @param CompanyAccount $model
     * @return $this
     */
    public function store(CompanyAccount $model): self;
}
