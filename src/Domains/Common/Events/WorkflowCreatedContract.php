<?php

namespace Domains\Common\Events;

use Domains\Common\Models\WorkflowContract;

interface WorkflowCreatedContract
{
    /**
     * @return WorkflowContract
     */
    public function workflow(): WorkflowContract;
}
