<?php

namespace Domains\Common\Models\Workflow;

use Domains\Common\Models\AggregateRootId;

interface StageContract
{
    /**
     * @return mixed
     */
    public function uuid(): AggregateRootId;

    /**
     * @return string
     */
    public function name(): string;
}
