<?php

namespace Domains\Common\Models;

use Doctrine\Common\Collections\Collection;
use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\Workflow\StageContract;

interface WorkflowContract
{
    /**
     * @return AggregateRootId
     */
    public function uuid(): AggregateRootId;

    /**
     * @return CompanyAccountContract
     */
    public function companyAccount(): CompanyAccountContract;

    /**
     * @param AggregateRootId $stageId
     * @return StageContract|null
     */
    public function findStage(AggregateRootId $stageId): ?StageContract;

    /**
     * @return Collection
     */
    public function stages(): Collection;
}
