<?php

namespace Domains\Sales\Repositories;

use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Sales\Models\Workflow\Workflow;
use Domains\Sales\Models\Workflow\WorkflowId;

interface WorkflowRepositoryContract
{
    /**
     * Create default workflow
     * @param CompanyAccountContract $companyAccount
     * @return Workflow
     */
    public function createDefault(CompanyAccountContract $companyAccount): Workflow;

    /**
     * Find by id
     * @param WorkflowId $id
     * @return Workflow|null
     */
    public function findById(WorkflowId $id): ?Workflow;

    /**
     * Unlink stage from workflow and leads
     * @param AggregateRootId $stageId
     * @param AggregateRootId $moveToStageId
     * @return $this
     */
    public function unlinkStage(AggregateRootId $stageId, AggregateRootId $moveToStageId): self;

    /**
     * Create or update given workflow
     * @param Workflow $model
     * @return $this
     */
    public function store(Workflow $model): self;

    /**
     * Remove given workflow from data storage
     * @param Workflow $model
     * @return $this
     */
    public function remove(Workflow $model): self;

    /**
     * Flush changes to data storage
     * @return $this
     */
    public function flush(): self;
}
