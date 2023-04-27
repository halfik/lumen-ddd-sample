<?php

namespace Infrastructure\Persistence\Doctrine\Repositories\AdminPanel;

use Domains\AdminPanel\Repositories\CompanyUserRepositoryContract;
use Domains\Common\Models\Paginator;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Infrastructure\Persistence\Doctrine\Repositories\DoctrineLocalizeTimezone;

class DoctrineCompanyUserRepository extends EntityRepository implements CompanyUserRepositoryContract
{
    use DoctrineLocalizeTimezone;

    /**
     * @inheritDoc
     */
    public function list(int $page, int $perPage, ?string $searchPhrase): Paginator
    {
        $offset = ($page - 1) * $perPage;
        $builder = $this->createQueryBuilder('UserCompanyAccount')
                    ->innerJoin('UserCompanyAccount.user', 'User');

        if ($searchPhrase) {
            $builder->where("ILIKE(User.displayName, :searchPhrase) = TRUE")
              ->orWhere("ILIKE(User.email, :searchPhrase) = TRUE")
              ->setParameter('searchPhrase', "%$searchPhrase%");
        }

        $builder->orderBy('User.displayName', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($perPage);

        $paginator = new DoctrinePaginator($builder->getQuery());
        $results = $paginator->getQuery()->getResult();

        return new Paginator($results, count($paginator), $page, $perPage);
    }
}
