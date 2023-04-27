<?php

namespace Infrastructure\Persistence\Doctrine\Repositories\Accounts;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Domains\Accounts\Models\Company\CompanyAccountId;
use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Accounts\Models\User\UserId;
use Domains\Accounts\Repositories\UserRepositoryContract;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Paginator;
use Infrastructure\Persistence\Doctrine\Repositories\DoctrineLocalizeTimezone;

class DoctrineUserRepository extends EntityRepository implements UserRepositoryContract
{
    use DoctrineLocalizeTimezone;

    /**
     * @inheritDoc
     * @throws NonUniqueResultException
     */
    public function findOneByEmail(string $email, ?AggregateRootId $editedUserId = null): ?UserContract
    {
        $builder = $this->createQueryBuilder('User')
            ->where('LOWER(User.email) = LOWER(:email)')
            ->setParameter('email', $email);

        if ($editedUserId) {
            $builder->andWhere('User.uuid != :editedUserId')
                    ->setParameter('editedUserId', (string)$editedUserId);
        }

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     * @throws NonUniqueResultException
     */
    public function findById(UserId $userId): ?UserContract
    {
        $builder = $this->createQueryBuilder('User')
            ->where('User.uuid = :uuid')
            ->setParameter('uuid', (string)$userId);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByIds(array $userIds): array
    {
        $builder = $this->createQueryBuilder('User')
            ->where('User.uuid IN  (:uuidList)')
            ->setParameter( 'uuidList', $userIds);

        $results = $builder->getQuery()->getResult();
        return is_array($results) ? $results : [];
    }

    /**
     * @inheritDoc
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     * @throws Exception
     */
    public function list(
        CompanyAccountId $companyAccountId,
        UserCompanyAccountStatus $status,
        int $page,
        int $itemsPerPage,
        ?string $searchPhrase = null,
        ?array $limitedToRoles = null,
    ): Paginator
    {
        $em = $this->getEntityManager();

        $sql = 'SELECT user__id FROM users__company_accounts
            INNER JOIN users ON users__company_accounts.user__id=users.id
            WHERE company_account__id=:companyId AND status=:status
            ';

        $queryParams = [
            'companyId' => (string)$companyAccountId,
            'status' => (string)$status,
        ];

        if ($limitedToRoles) {
            $tempArray = array_map(function ($el) {
                return '\'' . $el . '\'';
            }, $limitedToRoles);

            $sql .= ' AND role IN (' . implode(', ', $tempArray) . ')';
        }

        if ($searchPhrase) {
            $sql .= ' AND (
                        display_name ILIKE :search OR
                        email ILIKE :search OR
                        phone_number ILIKE :search
                    )';

            $queryParams['search'] = "%$searchPhrase%";
        }

        $statement = $em->getConnection()->prepare($sql);
        $userIds = $statement->executeQuery($queryParams)->fetchFirstColumn();
        $offset = ($page - 1) * $itemsPerPage;

        $builder = $this->createQueryBuilder('User')
            ->where('User.uuid IN (:ids)')
            ->orderBy('User.displayName', 'ASC')
            ->setFirstResult($offset)
            ->setMaxResults($itemsPerPage)
            ->setParameter('ids', $userIds);

        $results = $builder->getQuery()->getResult();

        return new Paginator($results, count($userIds), $page, $itemsPerPage);
    }

    /**
     * @inheritDoc
     */
    public function store(UserContract $model): UserRepositoryContract
    {
        $this->getEntityManager()->persist($model);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flush(): UserRepositoryContract
    {
        $this->getEntityManager()->flush();
        return $this;
    }
}
