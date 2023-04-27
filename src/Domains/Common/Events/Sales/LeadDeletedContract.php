<?php

namespace Domains\Common\Events\Sales;

use Domains\Common\Models\Lead\LeadContract;

interface LeadDeletedContract
{
    /**
     * @return LeadContract
     */
    public function lead(): LeadContract;
}
