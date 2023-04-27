<?php

namespace Infrastructure\Persistence\Doctrine\Repositories\Sales;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityRepository;
use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Sales\Models\Workflow\Workflow;
use Domains\Sales\Models\Workflow\WorkflowId;
use Domains\Sales\Repositories\WorkflowRepositoryContract;

class DoctrineWorkflowRepository extends EntityRepository implements WorkflowRepositoryContract
{
    /**
     * @param CompanyAccountContract $companyAccount
     * @return Workflow
     * @throws \Exception
     */
    public function createDefault(CompanyAccountContract $companyAccount): Workflow
    {
        return Workflow::default($companyAccount);
    }

    /**
     * @inheritDoc
     */
    public function findById(WorkflowId $id): ?Workflow
    {
        $builder = $this->createQueryBuilder('Workflow')
            ->where('Workflow.uuid = :uuid')
            ->setParameter('uuid', (string)$id);

        return $builder->getQuery()->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     * @throws \Exception
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function unlinkStage(AggregateRootId $stageId, AggregateRootId $moveToStageId): WorkflowRepositoryContract
    {
        $em = $this->getEntityManager();

        $params = [
          'oldStage' => (string)$stageId,
          'newStage' => (string)$moveToStageId,
        ];

        $sql = 'UPDATE leads
                SET
                    stage__id = :newStage,
                    version = version + 1,
                    updated_at = now(),
                    stage_changed_at = now()
                WHERE stage__id = :oldStage
        ';

        $em->getConnection()
            ->prepare($sql)
            ->executeQuery($params);

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function store(Workflow $model): WorkflowRepositoryContract
    {
        $this->getEntityManager()->persist($model);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function remove(Workflow $model): WorkflowRepositoryContract
    {
        $this->getEntityManager()->remove($model);
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function flush(): WorkflowRepositoryContract
    {
        $this->getEntityManager()->flush();
        return $this;
    }
}
