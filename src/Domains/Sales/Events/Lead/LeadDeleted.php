<?php

namespace Domains\Sales\Events\Lead;

use Domains\Common\Events\DomainEvent;
use Domains\Common\Events\Sales\LeadDeletedContract;
use Domains\Common\Models\Lead\LeadContract;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Sales\Events\SalesEventContract;

class LeadDeleted extends DomainEvent implements
    SalesEventContract,
    LeadDeletedContract
{
    private const VERSION = 2;

    /**
     * @param ActionPerformedByContract $performedBy
     * @param LeadContract $performedOn
     */
    public function __construct(private ActionPerformedByContract $performedBy, private LeadContract $performedOn)
    {
    }

    /**
     * @return LeadContract
     */
    public function lead(): LeadContract
    {
        return $this->performedOn;
    }

    /**
     * @return ActionPerformedByContract
     */
    public function performedBy(): ActionPerformedByContract
    {
        return $this->performedBy;
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
        return self::ACTION_DELETED;
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
        $data['lead_id'] = (string)$this->lead()->uuid();

        return $data;
    }

    public function type(): string
    {
        return strtolower(sprintf('%s.%s.%s', $this->domain(), $this->model(), $this->action()));
    }
}
