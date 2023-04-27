<?php

namespace App\Http\Responses\Sales;

use App\Http\Mappers\Sales\ApiWorkflow;
use Domains\Sales\Models\Workflow\Workflow;
use Illuminate\Http\JsonResponse;

class CreateStageResponse  extends JsonResponse
{
    public function __construct(Workflow $workflow)
    {
        $apiModel = new ApiWorkflow($workflow);
        parent::__construct($apiModel->toArray(), self::HTTP_CREATED);
    }
}
