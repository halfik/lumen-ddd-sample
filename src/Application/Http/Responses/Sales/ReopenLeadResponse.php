<?php

namespace App\Http\Responses\Sales;

use App\Http\Mappers\Sales\ApiLead;
use Domains\Common\Models\Lead\LeadContract;
use Illuminate\Http\JsonResponse;

class ReopenLeadResponse extends JsonResponse
{
    public function __construct(LeadContract $lead)
    {
        $apiLead = new ApiLead($lead);
        parent::__construct($apiLead->toArray(), self::HTTP_ACCEPTED);
    }
}
