<?php

namespace App\Http\Mappers\Sales;

use App\Http\Mappers\Accounts\ApiUser;
use App\Http\Mappers\AddressBook\ApiContact;
use Domains\Common\Models\Lead\LeadContract;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Common\Models\Permission\PermissionsContract;
use OpenApi\Annotations as OA;

/**
 *  @OA\Schema(
 *     schema="Lead",
 *     type="object",
 *     @OA\Property (
 *       property="id",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="title",
 *       type="string",
 *     ),
 *     @OA\Property (
 *       property="stage",
 *       type="object",
 *       ref="#/components/schemas/LeadStage"
 *     ),
 *     @OA\Property (
 *       property="created_by",
 *       type="object",
 *       ref="#/components/schemas/LeadCreatedBy"
 *     ),
 *     @OA\Property (
 *       property="owner",
 *       type="object",
 *       ref="#/components/schemas/LeadOwner"
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
 *       property="planned_close_at",
 *       type="integer",
 *     ),
 *     @OA\Property (
 *       property="closed_at",
 *       type="integer",
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
 *       property="assigned_at",
 *       type="integer",
 *     ),
 *     @OA\Property (
 *       property="stage_changed_at",
 *       type="integer",
 *     ),
 *  ),
 *
 * @OA\Schema(
 *    schema="LeadWithActions",
 *    type="object",
 *    allOf={
 *      @OA\Schema(ref="#/components/schemas/Lead"),
 *      @OA\Schema(
 *         @OA\Property (
 *             property="allowed_actions",
 *             type="array",
 *             @OA\Items(
 *               type="string",
 *             )
 *         ),
 *      )
 *   }
 * )
 *
 * @OA\Schema(
 *   schema="LeadList",
 *   type="object",
 *   @OA\Property(
 *     property="current_page",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="last_page",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="per_page",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *     property="total",
 *     type="integer",
 *   ),
 *   @OA\Property(
 *      property="data",
 *      type="array",
 *      @OA\Items(ref="#/components/schemas/LeadWithActions")
 *   ),
 * ),
 */
class ApiLead
{
    private LeadContract $lead;
    private bool $withOwner;
    private bool $withStage;
    private ?ActionPerformedByContract $actionPerformedBy;

    public function __construct(LeadContract $lead)
    {
        $this->lead = $lead;
        $this->withOwner = false;
        $this->withStage = false;
        $this->actionPerformedBy = null;
    }

    /**
     * @param bool $withOwner
     * @return $this
     */
    public function withOwner(bool $withOwner): self
    {
        $this->withOwner = $withOwner;
        return $this;
    }

    /**
     * @param bool $withStage
     * @return $this
     */
    public function withStage(bool $withStage): self
    {
        $this->withStage = $withStage;
        return $this;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function toArray(): array
    {
        $row = [
            'id' => (string)$this->lead->uuid(),
            'title' => $this->lead->title(),
            'stage' => [
                'id' => (string)$this->lead->stage()->uuid(),
            ],
            'created_by' => [
                'id' => (string)$this->lead->createdBy()->uuid(),
                'display_name' =>  $this->lead->createdBy()->displayName(),
            ],
            'owner' => [
                'id' => (string)$this->lead->owner()->uuid(),
                'display_name' =>  $this->lead->owner()->displayName(),
            ],
            'estimated_revenue' => $this->lead->estimatedRevenue() ? $this->lead->estimatedRevenue()->value(true) : null,
            'actual_revenue' => $this->lead->actualRevenue()->value(true),
            'planned_close_at' => $this->lead->plannedCloseAt()->getTimestamp(),
            'closed_at' => $this->lead->closedAt() ? $this->lead->closedAt()->getTimestamp() : null,
            'created_at' => $this->lead->createdAt()->getTimestamp(),
            'updated_at' => $this->lead->updatedAt()->getTimestamp(),
            'assigned_at' => $this->lead->assignedAt()->getTimestamp(),
            'stage_changed_at' => $this->lead->stageChangedAt()->getTimestamp(),
        ];

        if ($this->withOwner)  {
            $apiOwner = new ApiUser($this->lead->owner(), true);
            $row['owner'] = $apiOwner->toArray();
        }

        if ($this->withStage)  {
            $apiStage = new ApiWorkflowStage($this->lead->stage());
            $row['stage'] = $apiStage->toArray();
        }

        return $row;
    }
}
