<?php

namespace Infrastructure\Persistence\Doctrine\Repositories\Sales;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\ORM\EntityRepository;
use Domains\Accounts\Models\User\UserId;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Lead\LeadContract;
use Domains\Common\Models\Paginator;
use Domains\Common\Models\WorkflowContract;
use Domains\Sales\Models\Lead\LeadId;
use Domains\Sales\Models\Workflow\Stage;
use Domains\Sales\Repositories\LeadRepositoryContract;

class DoctrineLeadRepository extends EntityRepository implements LeadRepositoryContract
{
    /**
     * @inheritDoc
     */
    public function findById(LeadId $leadId, ?AggregateRootId $companyAccountId = null): ?LeadContract
    {
        $params = [
            'lead_id' => (string)$leadId
        ];
        $builder = $this->createQueryBuilder('Lead')
            ->andWhere('Lead.uuid = :lead_id');

        if ($companyAccountId) {
            $params['company_account_id'] = (string)$companyAccountId;
            $builder->andWhere('Lead.companyAccount = :company_account_id');
        }

        $builder->setParameters($params);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByIds(AggregateRootId $companyAccountId, array $ids, ?string $orderBy=null, string $orderDir='ASC'): array
    {
        $builder = $this->createQueryBuilder('Lead')
            ->where('Lead.companyAccount = :companyAccountId')
            ->andWhere('Lead.uuid IN (:ids)')
            ->setParameters([
                'companyAccountId' => (string)$companyAccountId,
                'ids' => $ids,
            ]);

        if($orderBy) {
            $builder->orderBy('Lead.'.$orderBy, $orderDir);
        }

        return $builder->getQuery()->getResult();
    }

    public function store(LeadContract $model): LeadRepositoryContract
    {
        $this->getEntityManager()->persist($model);

        return $this;
    }

    public function flush(): LeadRepositoryContract
    {
        $this->getEntityManager()->flush();

        return $this;
    }
}
