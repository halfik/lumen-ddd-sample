<?php

namespace Domains\Sales\Events\Workflow;

use Domains\Common\Events\DomainEvent;
use Domains\Sales\Events\SalesEventContract;
use Domains\Sales\Models\Workflow\Stage;

class StageMoved extends DomainEvent implements SalesEventContract
{
    private const VERSION = 2;

    private Stage $stage;
    private int $oldPosition;

    /**
     * @param Stage $stage
     * @param int   $oldPosition
     */
    public function __construct(Stage $stage, int $oldPosition)
    {
        $this->stage = $stage;
        $this->oldPosition = $oldPosition;
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
    public function oldPosition(): int
    {
        return $this->oldPosition;
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
        $data['old_position'] = $this->oldPosition();

        return $data;
    }

    public function type(): string
    {
        return strtolower(
            sprintf(
                '%s.%s.%s',
                self::DOMAIN_SALES,
                self::MODEL_WORKFLOW_STAGE,
                self::ACTION_MOVED
            )
        );
    }
}
