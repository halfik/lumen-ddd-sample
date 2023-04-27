<?php

namespace App\Http\Mappers\AdminPanel\Sales;

use Domains\AdminPanel\Models\Sales\Workflow;

/**
 *  @OA\Schema (
 *     schema="AdminPanelWorkflow",
 *     type="object",
 *     @OA\Property (
 *       property="id",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="name",
 *       type="string",
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
        return [
            'id' => (string)$this->workflow->uuid(),
            'name' => $this->workflow->name(),
        ];
    }
}
