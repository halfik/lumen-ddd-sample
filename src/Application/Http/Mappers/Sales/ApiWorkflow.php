<?php

namespace App\Http\Mappers\Sales;

use Domains\Sales\Models\Workflow\Workflow;

/**
 *  * @OA\Schema (
 *     schema="Workflow",
 *     type="object",
 *     @OA\Property (
 *       property="id",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="name",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="created_at",
 *       type="integer",
 *     ),
 *     @OA\Property (
 *       property="updated_at",
 *       type="integer",
 *     ),
 *     @OA\Property (
 *        property="stages",
 *        type="array",
 *        @OA\Items(
 *           ref="#/components/schemas/WorkflowStage"
 *        ),
 *     ),
 * )
 */
class ApiWorkflow
{
    private Workflow $workflow;

    public function __construct(Workflow $workflow)
    {
        $this->workflow = $workflow;
    }

    public function toArray(): array
    {
        $row = [
            'id' => (string)$this->workflow->uuid(),
            'name' => $this->workflow->name(),
            'created_at' => $this->workflow->createdAt()->getTimestamp(),
            'updated_at' => $this->workflow->updatedAt()->getTimestamp(),
            'stages' => [],
        ];

        foreach ($this->workflow->stages() as $stage) {
            $apiStage = new ApiWorkflowStage($stage);
            $row['stages'][] = $apiStage->toArray();
        }

        return $row;
    }
}
