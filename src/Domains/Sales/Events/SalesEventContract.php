<?php

namespace Domains\Sales\Events;

interface SalesEventContract
{
    public const ACTION_CLOSED = 'closed';
    public const ACTION_CREATED = 'created';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_EDITED = 'edited';
    public const ACTION_MOVED = 'moved';

    public const DOMAIN_SALES = 'Sales';

    public const MODEL_LEAD = 'Lead';
    public const MODEL_WORKFLOW = 'Workflow';
    public const MODEL_WORKFLOW_STAGE = 'Stage';
}
