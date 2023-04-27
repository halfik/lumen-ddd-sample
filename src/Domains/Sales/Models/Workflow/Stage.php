<?php

namespace Domains\Sales\Models\Workflow;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domains\Common\Exceptions\DomainAuthException;
use Domains\Common\Exceptions\DomainException;
use Domains\Common\Exceptions\ValidationException;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Common\Models\SoftDeletableTrait;
use Domains\Common\Models\Workflow\StageContract;
use Domains\Sales\Events;
use Domains\Sales\Models\Permissions\ActionPermissions;
use Domains\Sales\Models\Revenue;
use Domains\Sales\Repositories\WorkflowRepositoryContract;
use Domains\Sales\Validation\StageValidator;

class Stage extends AggregateRoot implements StageContract
{
    use SoftDeletableTrait;

    private Workflow $workflow;
    private StageType $type;
    private string $name;
    private int $position;

    private Revenue $estimatedRevenue;
    private Revenue $actualRevenue;

    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private Collection $leads;

    /**
     * @param StageId   $id
     * @param Workflow  $workflow
     * @param StageType $type
     * @param string    $name
     * @param int       $position
     * @param int       $version
     * @throws DomainException
     */
    public function __construct(
        StageId $id,
        Workflow $workflow,
        StageType $type,
        string $name,
        int $position,
        int $version = 1
    )
    {
        parent::__construct($id, $version);

        $this->workflow = $workflow;
        $this->type = $type;
        $this->name = $name;
        $this->position = $position;

        $this->actualRevenue = new Revenue(0);
        $this->estimatedRevenue = new Revenue(0);

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $this->leads = new ArrayCollection();

        $event = new Events\Workflow\StageCreated($this);
        $this->workflow->applyStageCreated($event);
    }

    /**
     * Create new stage
     * @param WorkflowRepositoryContract $workflowRepository
     * @param ActionPerformedByContract  $performedBy
     * @param Workflow                   $workflow
     * @param string                     $name
     * @param int|null                   $position
     * @return static
     * @throws DomainAuthException
     * @throws DomainException
     * @throws ValidationException
     */
    public static function createNew(
        WorkflowRepositoryContract $workflowRepository,
        ActionPerformedByContract $performedBy,
        Workflow $workflow,
        string $name,
        ?int $position
    ): self
    {
        if (!$performedBy->isMemberOf($workflow->companyAccount())) {
            throw DomainAuthException::notMemberOfCompany($workflow->companyAccount()->uuid());
        }

        self::checkPermission(
            new ActionPermissions\CreateStage(),
            new ActionContext($performedBy, $workflow)
        );

        $maxPosition = $workflow->stages()->count() + 1;
        if(is_null($position) || $position > $maxPosition) {
            $position = $maxPosition;
        }

        $validator = new StageValidator();
        $validator->name($name)
            ->position($position);

        if ($validator->exception()->hasErrors()) {
            throw $validator->exception();
        }

        $model = new self(
            StageId::nextId(),
            $workflow,
            $position === $maxPosition ? StageType::closure() : StageType::normal(),
            $name,
            $position,
        );
        $workflowRepository->store($workflow);

        return $model;
    }

    /**
     * Edit stage
     * @param WorkflowRepositoryContract $workflowRepository
     * @param ActionPerformedByContract  $performedBy
     * @param string                     $name
     * @return void
     * @throws DomainAuthException
     * @throws ValidationException
     */
    public function edit(
        WorkflowRepositoryContract $workflowRepository,
        ActionPerformedByContract $performedBy,
        string $name
    ): void
    {
        if (!$performedBy->isMemberOf($this->workflow()->companyAccount())) {
            throw DomainAuthException::notMemberOfCompany($this->workflow()->companyAccount()->uuid());
        }

        self::checkPermission(
            new ActionPermissions\EditStage(),
            new ActionContext($performedBy, $this->workflow())
        );

        $validator = new StageValidator();
        $validator->name($name);

        if ($validator->exception()->hasErrors()) {
            throw $validator->exception();
        }

        $this->name = $name;
        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();

        $workflowRepository->store($this->workflow());
    }

    /**
     * Check if stage is last in workflow
     * @return bool
     */
    public function isLast(): bool
    {
        /** @var Stage $last */
        $last = null;
        foreach ($this->workflow()->stages() as $stage) {
            if (!$last || $stage->position() > $last->position() ) {
                $last = $stage;
            }
        }

        return $last->uuid()->equals(
            $this->uuid()
        );
    }

    /**
     * Check if stage is first in workflow
     * @return bool
     */
    public function isFirst(): bool
    {
        /** @var Stage $first */
        $first = null;
        foreach ($this->workflow()->stages() as $stage) {
            if (!$first || $stage->position() < $first->position() ) {
                $first = $stage;
            }
        }

        return $first->uuid()->equals(
            $this->uuid()
        );
    }

    /**
     * Lead was created on workflow
     * @param Events\Lead\LeadCreated $event
     * @throws DomainException
     */
    public function applyLeadCreated(mixed $event): void
    {
        if (!$this->uuid()->equals($event->lead()->stage()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong workflow: %s - %s',
                (string)$this->uuid(),
                (string)$event->lead()->stage()->workflow()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        $this->leads->add($event->lead());
    }

    /**
     * @param Events\Lead\LeadDeleted $event
     * @throws DomainException
     */
    public function applyLeadDeleted(Events\Lead\LeadDeleted $event): void
    {
        if (!$this->uuid()->equals($event->lead()->stage()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong workflow: %s - %s',
                (string)$this->uuid(),
                (string)$event->lead()->stage()->workflow()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        /**
         * @var int $index
         * @var AggregateRoot $row
         */
        foreach ($this->leads as $index => $row) {
            if ($row->uuid()->equals($event->lead()->uuid())) {
                $this->leads->remove($index);
                break;
            }
        }
    }

    /**
     * @param Events\Workflow\StageCreated $event
     * @throws DomainException
     */
    public function applyStageCreated(Events\Workflow\StageCreated $event): void
    {
        if (!$this->workflow()->uuid()->equals($event->stage()->workflow()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong workflow: %s - %s',
                (string)$this->uuid(),
                (string)$event->stage()->workflow()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        $changes = false;
        // reposition stage
        if($event->stage()->position() <= $this->position()) {
            $this->position++;
            $changes = true;
        }

        // ensure only 1 closure
        if(
            $event->stage()->type()->same(StageType::closure())
            &&
            $this->type()->same(StageType::closure())
        ) {
            $changes = true;
            $this->type = StageType::normal();
        }

        if ($changes) {
            $this->updatedAt = new \DateTimeImmutable();
            $this->nextVersion();
        }
    }

    /**
     * @param Events\Workflow\StageDeleted $event
     * @throws DomainException
     */
    public function applyStageDeleted(Events\Workflow\StageDeleted $event): void
    {
        if (!$this->workflow()->uuid()->equals($event->stage()->workflow()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong workflow: %s - %s',
                (string)$this->uuid(),
                (string)$event->stage()->workflow()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        $changes = false;
        if ($event->stage()->position() <= $this->position()) {
            $this->position--;
            $changes = true;
        }

        //ensure 1 closure stage exists
        if ($event->stage()->type()->same(StageType::closure()) && $this->isLast()) {
            $this->type = StageType::closure();
            $changes = true;
        }

        if ($changes) {
            $this->updatedAt = new \DateTimeImmutable();
            $this->nextVersion();
        }
    }

    /**
     * @param Events\Workflow\StageMoved $event
     * @throws DomainException
     */
    public function applyStageMoved(Events\Workflow\StageMoved $event): void
    {
        if (!$this->workflow()->uuid()->equals($event->stage()->workflow()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong workflow: %s - %s',
                (string)$this->uuid(),
                (string)$event->stage()->workflow()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        $movedStage = $event->stage();
        $maxPosition = $this->workflow()->stages()->count();
        if ($movedStage->uuid()->equals($this->uuid())) {
            return;
        }

        // elements between old and new position are affected
        $min = min($movedStage->position(), $event->oldPosition());
        $max = max($movedStage->position(), $event->oldPosition());

        // affected stages
        if($this->position() <= $max && $this->position() >= $min) {
            // moved was up
            if ($movedStage->position() >= $event->oldPosition()) {
                $this->position--;
            } else {
                // move was down
                $this->position++;
            }
        }

        $this->type = $this->position() === $maxPosition ? StageType::closure() : StageType::normal();
        $this->updatedAt = new \DateTimeImmutable();
        $this->nextVersion();
    }

    /**
     * @return Workflow
     */
    public function workflow(): Workflow
    {
        return $this->workflow;
    }

    /**
     * @return StageType
     */
    public function type(): StageType
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function position(): int
    {
        return $this->position;
    }

    /**
     * @return Revenue
     */
    public function estimatedRevenue(): Revenue
    {
        return $this->estimatedRevenue;
    }

    /**
     * @return Revenue
     */
    public function actualRevenue(): Revenue
    {
        return $this->actualRevenue;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * @return Collection
     */
    public function leads(): Collection
    {
        return $this->leads;
    }
}
