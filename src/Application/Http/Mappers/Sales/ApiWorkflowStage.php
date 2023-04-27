<?php

namespace App\Http\Mappers\Sales;

use Domains\Sales\Models\Workflow\Stage;

/**
 *  * @OA\Schema (
 *     schema="WorkflowStage",
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
 *       property="type",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="position",
 *       type="integer",
 *     ),
 *     @OA\Property (
 *       property="estimated_revenue",
 *       type="number",
 *     ),
 *     @OA\Property (
 *       property="actual_revenue",
 *       type="number",
 *     ),
 *     @OA\Property (
 *       property="created_at",
 *       type="integer",
 *     ),
 *     @OA\Property (
 *       property="updated_at",
 *       type="integer",
 *     )
 *  ),
 *
 * @OA\Schema (
 *     schema="LeadStage",
 *     type="object",
 *     @OA\Property (
 *       property="id",
 *       type="string",
 *     ),
 *   ),
 */
class ApiWorkflowStage
{
    private Stage $stage;

    public function __construct(Stage $stage)
    {
        $this->stage = $stage;
    }

    public function toArray(): array
    {
        return [
            'id' => (string)$this->stage->uuid(),
            'position' => $this->stage->position(),
            'type' => (string)$this->stage->type(),
            'name' => $this->stage->name(),
            'estimated_revenue' => $this->stage->estimatedRevenue()->value(true),
            'actual_revenue' => $this->stage->actualRevenue()->value(true),
            'created_at' => $this->stage->createdAt()->getTimestamp(),
            'updated_at' => $this->stage->updatedAt()->getTimestamp(),
        ];
    }
}
