<?php

namespace Infrastructure\Persistence\Doctrine\Repositories\AdminPanel;

use Doctrine\ORM\EntityRepository;
use Domains\AdminPanel\Models\Accounts\User;
use Domains\AdminPanel\Repositories\UserRepositoryContract;
use Domains\Common\Models\AggregateRootId;

class DoctrineUserRepository extends EntityRepository implements UserRepositoryContract
{
    /**
     * @inheritDoc
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findOneByEmail(string $email, ?AggregateRootId $editedUserId = null): ?User
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
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function findById(AggregateRootId $userId): ?User
    {
        $builder = $this->createQueryBuilder('User')
            ->where('User.uuid = :uuid')
            ->setParameter('uuid', (string)$userId);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function store(User $model): UserRepositoryContract
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
