<?php

namespace Infrastructure\Persistence\Doctrine\Repositories\AdminPanel;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Domains\AdminPanel\Models\Accounts\CompanyAccount;
use Domains\AdminPanel\Repositories\CompanyAccountRepositoryContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Paginator;

class DoctrineCompanyAccountRepository extends EntityRepository implements CompanyAccountRepositoryContract
{
    /**
     * @inheritDoc
     */
    public function findById(AggregateRootId $companyAccountId): ?CompanyAccount
    {
        $builder = $this->createQueryBuilder('CompanyAccount')
            ->where('CompanyAccount.uuid = :uuid')
            ->setParameter('uuid', (string)$companyAccountId);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function list(int $page, int $perPage, ?string $searchPhrase): Paginator
    {
        $offset = ($page - 1) * $perPage;
        $builder = $this->createQueryBuilder('CompanyAccount');

        if ($searchPhrase) {
            $builder->where("ILIKE(CompanyAccount.name, :searchPhrase) = TRUE")
                ->setParameter('searchPhrase', "%$searchPhrase%");
        }

        $builder->orderBy('CompanyAccount.name', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $paginator = new DoctrinePaginator($builder->getQuery());
        $results = $paginator->getQuery()->getResult();

        return new Paginator($results, count($paginator), $page, $perPage);
    }

    /**
     * @inheritDoc
     */
    public function store(CompanyAccount $model): CompanyAccountRepositoryContract
    {
        $this->getEntityManager()->persist($model);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flush(): CompanyAccountRepositoryContract
    {
        $this->getEntityManager()->flush();
        return $this;
    }
}
