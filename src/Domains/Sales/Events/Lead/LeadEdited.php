<?php

namespace Domains\Sales\Events\Lead;

use Domains\Common\Events\DomainEvent;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Lead\LeadContract;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Sales\Events\SalesEventContract;

class LeadEdited extends DomainEvent implements SalesEventContract
{
    private const VERSION = 2;

    /**
     * @param ActionPerformedByContract $performedBy
     * @param LeadContract $performedOn
     * @param array $oldValues
     */
    public function __construct(
        private ActionPerformedByContract $performedBy,
        private LeadContract $performedOn,
        private array $oldValues
    ) {
    }

    /**
     * @inheritDoc
     */
    public function performedOnId(): AggregateRootId
    {
        return $this->performedOn->uuid();
    }


    /**
     * @inheritDoc
     */
    public function domain(): string
    {
        return self::DOMAIN_SALES;
    }

    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return self::MODEL_LEAD;
    }

    /**
     * @inheritDoc
     */
    public function action(): string
    {
        return self::ACTION_EDITED;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return self::VERSION;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['lead_id'] = (string)$this->performedOnId();

        return $data;
    }

    public function type(): string
    {
        return strtolower(sprintf('%s.%s.%s', $this->domain(), $this->model(), $this->action()));
    }
}
