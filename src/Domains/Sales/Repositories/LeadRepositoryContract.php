<?php

namespace Domains\Sales\Repositories;

use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Lead\LeadContract;
use Domains\Sales\Models\Lead\LeadId;

interface LeadRepositoryContract
{
    /**
     * @param LeadId $leadId
     * @param null|AggregateRootId $companyAccountId
     * @return LeadContract|null
     */
    public function findById(LeadId $leadId, ?AggregateRootId $companyAccountId): ?LeadContract;

    /**
     * @param AggregateRootId $companyAccountId
     * @param LeadId[] $ids
     * @param ?string $orderBy
     * @param string $orderDir
     * @return LeadContract[]
     */
    public function findByIds(AggregateRootId $companyAccountId, array $ids, ?string $orderBy=null, string $orderDir='ASC'): array;

    /**
     * @param LeadContract $model
     * @return $this
     */
    public function store(LeadContract $model): self;

    /**
     * @return $this
     */
    public function flush(): self;
}
