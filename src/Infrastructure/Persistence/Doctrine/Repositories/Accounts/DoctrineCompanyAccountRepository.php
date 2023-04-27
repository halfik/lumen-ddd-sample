<?php

namespace Infrastructure\Persistence\Doctrine\Repositories\Accounts;

use Doctrine\ORM\EntityRepository;
use Domains\Accounts\Models\Company\CompanyAccount;
use Domains\Accounts\Repositories\CompanyAccountRepositoryContract;

class DoctrineCompanyAccountRepository extends EntityRepository implements CompanyAccountRepositoryContract
{
    public function store(CompanyAccount $model): CompanyAccountRepositoryContract
    {
        $this->getEntityManager()->persist($model);
        return $this;
    }
}
