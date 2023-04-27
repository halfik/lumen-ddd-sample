<?php

namespace Domains\Sales\Models\Workflow;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domains\Common\Exceptions\DomainException;
use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\WorkflowContract;
use Domains\Sales\Events;

class Workflow extends AggregateRoot implements WorkflowContract
{
    private string $name;

    private CompanyAccountContract $companyAccount;

    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;

    private Collection $stages;

    /**
     * @param WorkflowId     $id
     * @param CompanyAccountContract $companyAccount
     * @param string         $name
     */
    private function __construct(
        WorkflowId $id,
        CompanyAccountContract $companyAccount,
        string $name
    )
    {
        parent::__construct($id);

        $this->name = $name;
        $this->companyAccount = $companyAccount;
        $this->stages = new ArrayCollection();

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();

        $event = new Events\Workflow\WorkflowCreated($this);
        $companyAccount->applyWorkflowCreated($event);
    }

    /**
     * Create default workflow
     * @param CompanyAccountContract $companyAccount
     * @return Workflow
     * @throws \Exception
     */
    public static function default(CompanyAccountContract $companyAccount): Workflow
    {
        $workFlow = new Workflow(
            WorkflowId::nextId(),
            $companyAccount,
            'Default Workflow',
        );

        // Stages
        new Stage(
            StageId::nextId(),
            $workFlow,
            StageType::normal(),
            'Stage 1',
            1
        );

        new Stage(
            StageId::nextId(),
            $workFlow,
            StageType::normal(),
            'Stage 2',
            2
        );

        new Stage(
            StageId::nextId(),
            $workFlow,
            StageType::normal(),
            'Stage 3',
            3
        );

        new Stage(
            StageId::nextId(),
            $workFlow,
            StageType::normal(),
            'Stage 4',
            4
        );

        new Stage(
            StageId::nextId(),
            $workFlow,
            StageType::closure(),
            'Stage 5',
            5
        );

        return $workFlow;
    }

    /**
     * @param AggregateRootId $stageId
     * @return Stage|null
     */
    public function findStage(AggregateRootId $stageId): ?Stage
    {
        $stage = $this->stages()->filter(function(Stage $stageEntity) use($stageId) {
            return $stageEntity->uuid()->equals(
                $stageId
            );
        })->first();

        if (!$stage) {
            return null;
        }
        return $stage;
    }

    /**
     * Add stage
     * @param Events\Workflow\StageCreated $event
     * @throws DomainException
     */
    public function applyStageCreated(Events\Workflow\StageCreated $event): void
    {
        if (!$this->uuid()->equals($event->stage()->workflow()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong workflow: %s - %s',
                (string)$this->uuid(),
                (string)$event->stage()->workflow()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        // reposition older stages
        /** @var Stage $stage */
        foreach ($this->stages as $stage) {
            $stage->applyStageCreated($event);
        }
        $this->stages->add($event->stage());
    }

    /**
     * @param Events\Workflow\StageDeleted $event
     * @throws DomainException
     */
    public function applyStageDeleted(Events\Workflow\StageDeleted $event): void
    {
        if (!$this->uuid()->equals($event->stage()->workflow()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong workflow: %s - %s',
                (string)$this->uuid(),
                (string)$event->stage()->workflow()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        /**
         * @var int $index
         * @var AggregateRoot $row
         */
        foreach ($this->stages as $index => $row) {
            if ($row->uuid()->equals($event->stage()->uuid())) {
                $this->stages->remove($index);
                break;
            }
        }

        // reposition older stages
        /** @var Stage $stage */
        foreach ($this->stages as $stage) {
            $stage->applyStageDeleted($event);
        }
    }

    /**
     * Add stage
     * @param Events\Workflow\StageMoved $event
     * @throws DomainException
     */
    public function applyStageMoved(Events\Workflow\StageMoved $event): void
    {
        if (!$this->uuid()->equals($event->stage()->workflow()->uuid())) {
            $msg = sprintf(
                'Provided event applied on wrong workflow: %s - %s',
                (string)$this->uuid(),
                (string)$event->stage()->workflow()->uuid()
            );
            throw DomainException::eventOnWrongModel($msg);
        }

        // reposition older stages
        /** @var Stage $stage */
        foreach ($this->stages as $stage) {
            $stage->applyStageMoved($event);
        }
    }

    /**
     * @inheritDoc
     */
    public function companyAccount(): CompanyAccountContract
    {
        return $this->companyAccount;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
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
    public function stages(): Collection
    {
        return $this->stages;
    }

    /**
     * @return Stage
     */
    public function lastStage(): Stage
    {
        return $this->stages()->filter(function (Stage $stage){
            return $stage->isLast();
        })->first();
    }

    /**
     * @return Stage
     */
    public function firstStage(): Stage
    {
        return $this->stages()->filter(function (Stage $stage){
            return $stage->isFirst();
        })->first();
    }
}
