<?php

namespace Domains\Sales\Events\Workflow;

use Domains\Common\Events\DomainEvent;
use Domains\Common\Events\WorkflowCreatedContract;
use Domains\Sales\Events\SalesEventContract;
use Domains\Sales\Models\Workflow\Workflow;

class WorkflowCreated extends DomainEvent implements WorkflowCreatedContract, SalesEventContract
{
    private const VERSION = 2;

    private Workflow $workflow;

    /**
     * @param Workflow $workflow
     */
    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    /**
     * @return Workflow
     */
    public function workflow(): Workflow
    {
        return $this->workflow;
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
        $data['workflow_id'] = (string)$this->workflow()->uuid();

        return $data;
    }

    public function type(): string
    {
        return strtolower(
            sprintf(
                '%s.%s.%s',
                self::DOMAIN_SALES,
                self::MODEL_WORKFLOW,
                self::ACTION_CREATED
            )
        );
    }
}
