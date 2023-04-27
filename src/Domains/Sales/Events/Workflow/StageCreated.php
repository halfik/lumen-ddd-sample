<?php

namespace Domains\Sales\Events\Workflow;

use Domains\Common\Events\DomainEvent;
use Domains\Sales\Events\SalesEventContract;
use Domains\Sales\Models\Workflow\Stage;

class StageCreated extends DomainEvent implements SalesEventContract
{
    private const VERSION = 2;

    private Stage $stage;

    /**
     * @param Stage $stage
     */
    public function __construct(Stage $stage)
    {
        $this->stage = $stage;
    }

    /**
     * @return Stage
     */
    public function stage(): Stage
    {
        return $this->stage;
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
        $data['stage_id'] = (string)$this->stage()->uuid();

        return $data;
    }

    public function type(): string
    {
        return strtolower(
            sprintf(
                '%s.%s.%s',
                self::DOMAIN_SALES,
                self::MODEL_WORKFLOW_STAGE,
                self::ACTION_CREATED
            )
        );
    }
}

