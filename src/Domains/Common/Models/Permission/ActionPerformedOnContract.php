<?php

namespace Domains\Common\Models\Permission;

use Domains\Common\Models\AggregateRootId;

interface ActionPerformedOnContract
{
    /**
     * @return AggregateRootId
     */
    public function uuid(): AggregateRootId;
}
