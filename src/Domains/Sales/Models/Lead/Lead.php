<?php

namespace Domains\Sales\Models\Lead;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domains\Common\Exceptions\DomainAuthException;
use Domains\Common\Exceptions\DomainException;
use Domains\Common\Exceptions\ValidationException;
use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\Lead\LeadContract;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Common\Models\SoftDeletableTrait;
use Domains\Sales\Events;
use Domains\Sales\Models\Permissions\ActionPermissions;
use Domains\Sales\Models\Revenue;
use Domains\Sales\Models\Workflow\Stage;
use Domains\Sales\Models\Workflow\StageId;
use Domains\Sales\Models\Workflow\Workflow;
use Domains\Sales\Repositories\LeadRepositoryContract;
use Domains\Sales\Repositories\WorkflowRepositoryContract;
use Domains\Sales\Validation\LeadValidator;

class Lead extends AggregateRoot implements LeadContract
{
    use SoftDeletableTrait;

    protected string $title;

    protected CompanyAccountContract $companyAccount;

    protected Stage $stage;
    protected UserContract $owner;
    private UserContract $createdBy;

    protected ?Revenue $estimatedRevenue;
    protected Revenue $actualRevenue;

    protected \DateTimeImmutable $plannedCloseAt;
    protected ?\DateTimeImmutable $closedAt;

    private \DateTimeImmutable $createdAt;
    private \DateTimeImmutable $updatedAt;
    private \DateTimeImmutable $stageChangedAt;
    private \DateTimeImmutable $assignedAt;

    /**
     * @param LeadId $id
     * @param CompanyAccountContract $companyAccount
     * @param string $title
     * @param Stage $stage
     * @param UserContract $owner
     * @param UserContract $createdBy
     * @param Revenue|null $estimatedRevenue
     * @param \DateTimeImmutable $plannedCloseAt
     */
    private function __construct(
        LeadId $id,
        CompanyAccountContract $companyAccount,
        string $title,
        Stage $stage,
        UserContract $owner,
        UserContract $createdBy,
        ?Revenue $estimatedRevenue,
        \DateTimeImmutable $plannedCloseAt
    )
    {
        parent::__construct($id);

        $this->companyAccount = $companyAccount;
        $this->title = $title;

        // set by direct assignment instead of dedicated setOwner method to avoid triggering the LeadOwnerChanged event when importing leads
        $this->owner = $owner;
        $this->createdBy = $createdBy;


        $this->estimatedRevenue = $estimatedRevenue;
        $this->actualRevenue = new Revenue(0);

        $this->plannedCloseAt = $plannedCloseAt;
        $this->closedAt = null;

        $this->createdAt = new \DateTimeImmutable();
        $this->updatedAt = new \DateTimeImmutable();
        $this->assignedAt = new \DateTimeImmutable();
        $this->deletedAt = null;

        $this->setStage($stage);
    }

    /**
     * Create new lead
     *
     * @param WorkflowRepositoryContract $workflowRepository
     * @param string $title
     * @param Stage $stage
     * @param UserContract $owner
     * @param ActionPerformedByContract $createdBy
     * @param Revenue|null $estimatedRevenue
     * @param \DateTimeImmutable $plannedCloseAt
     * @return static
     * @throws DomainAuthException
     * @throws DomainException
     * @throws ValidationException
     */
    public static function createNewOne(
        WorkflowRepositoryContract $workflowRepository,
        string $title,
        Stage $stage,
        UserContract $owner,
        ActionPerformedByContract $createdBy,
        ?Revenue $estimatedRevenue,
        \DateTimeImmutable $plannedCloseAt
    ): self
    {
        $companyAccount = $stage->workflow()->companyAccount();
        self::checkPermission(
            new ActionPermissions\CreateLead(),
            new ActionContext($createdBy, $companyAccount)
        );

        $validator = new LeadValidator($stage->workflow());
        $validator->title($title)
            ->createdBy($createdBy)
            ->owner($owner)
            ->plannedCloseAt($plannedCloseAt);

        if ($validator->exception()->hasErrors()) {
            throw $validator->exception();
        }

        $lead = new self(
            LeadId::nextId(),
            $companyAccount,
            $title,
            $stage,
            $owner,
            $createdBy->user(),
            $estimatedRevenue,
            $plannedCloseAt
        );

        $workflowRepository->store($stage->workflow());

        $event = new Events\Lead\LeadCreated($createdBy, $lead);
        $lead->stage->applyLeadCreated($event);
        domainEvent($event);

        return $lead;
    }

    /**
     * Update lead
     *
     * @param WorkflowRepositoryContract $workflowRepository
     * @param ActionPerformedByContract $editedBy
     * @param UserContract $newOwner
     * @param string $newTitle
     * @param \DateTimeImmutable $newPlannedCloseAt
     * @throws DomainAuthException
     * @throws DomainException
     * @throws ValidationException
     */
    public function edit(
        WorkflowRepositoryContract $workflowRepository,
        ActionPerformedByContract $editedBy,
        UserContract $newOwner,
        string $newTitle,
        ?Revenue $newRevenue,
        \DateTimeImmutable $newPlannedCloseAt,
    ): void
    {
        if (!$editedBy->isMemberOf($this->companyAccount())) {
            throw DomainAuthException::notMemberOfCompany($this->companyAccount()->uuid());
        }

        self::checkPermission(
            new ActionPermissions\EditLead(),
            new ActionContext($editedBy, $this)
        );

        $validator = new LeadValidator($this->workflow());
        $validator->title($newTitle)
            ->owner($newOwner)
        ;

        if ($validator->exception()->hasErrors()) {
            throw $validator->exception();
        }

        $oldValues = $this->toArray();
        $this->setOwner($newOwner, $editedBy);
        $this->title = $newTitle;
        $this->plannedCloseAt = $newPlannedCloseAt;
        $this->estimatedRevenue = $newRevenue;
        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();

        $workflowRepository->store($this->stage()->workflow());

        $event = new Events\Lead\LeadEdited($editedBy, $this, $oldValues);
        domainEvent($event);
    }

    /**
     * Delete lead
     *
     * @param WorkflowRepositoryContract $workflowRepository
     * @param ActionPerformedByContract $deletedBy
     * @throws DomainAuthException
     * @throws DomainException
     */
    public function delete(
        WorkflowRepositoryContract $workflowRepository,
        ActionPerformedByContract $deletedBy
    ): void
    {
        if (!$deletedBy->isMemberOf($this->companyAccount())) {
            throw DomainAuthException::notMemberOfCompany($this->companyAccount()->uuid());
        }

        self::checkPermission(
            new ActionPermissions\DeleteLead(),
            new ActionContext($deletedBy, $this)
        );

        $this->deletedAt = new \DateTimeImmutable();
        $this->nextVersion();

        $event = new Events\Lead\LeadDeleted($deletedBy, $this);
        $this->stage->applyLeadDeleted($event);

        $workflowRepository->store($this->stage()->workflow());
        domainEvent($event);
    }

    /**
     * Close lead
     * @param WorkflowRepositoryContract $workflowRepository
     * @param ActionPerformedByContract  $closedBy
     * @param \DateTimeImmutable         $closeAt
     * @param Revenue|null               $actualRevenue
     * @throws DomainAuthException
     * @throws DomainException
     * @throws ValidationException
     */
    public function close(
        WorkflowRepositoryContract $workflowRepository,
        ActionPerformedByContract $closedBy,
        \DateTimeImmutable $closeAt,
        ?Revenue $actualRevenue
    ): void
    {
        if (!$closedBy->isMemberOf($this->companyAccount())) {
            throw DomainAuthException::notMemberOfCompany($this->companyAccount()->uuid());
        }

        self::checkPermission(
            new ActionPermissions\CloseLead(),
            new ActionContext($closedBy, $this)
        );

        $validator = new LeadValidator($this->workflow());
        $validator->closeAt($closeAt);

        if ($validator->exception()->hasErrors()) {
            throw $validator->exception();
        }

        $oldValues = $this->toArray();
        $this->actualRevenue = $actualRevenue ?? $this->actualRevenue;
        $this->closedAt =  $closeAt;
        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();

        $workflowRepository->store($this->stage()->workflow());
        $event = new Events\Lead\LeadClosed($closedBy, $this, $oldValues);
        domainEvent($event);
    }

    /**
     * Reopen closed lead
     * @param WorkflowRepositoryContract $workflowRepository
     * @param ActionPerformedByContract  $reopenedBy
     * @throws DomainAuthException
     */
    public function reopen(
        WorkflowRepositoryContract $workflowRepository,
        ActionPerformedByContract  $reopenedBy
    ): void
    {
        if (!$reopenedBy->isMemberOf($this->companyAccount())) {
            throw DomainAuthException::notMemberOfCompany($this->companyAccount()->uuid());
        }

        self::checkPermission(
            new ActionPermissions\CloseLead(),
            new ActionContext($reopenedBy, $this)
        );

        $this->actualRevenue = new Revenue(0);
        $this->closedAt =  null;
        $this->nextVersion();
        $this->updatedAt = new \DateTimeImmutable();

        $workflowRepository->store($this->stage()->workflow());
    }

    /**
     * @param WorkflowRepositoryContract $workflowRepository
     * @param ActionPerformedByContract $performedBy
     * @param Stage $newStage
     * @throws DomainAuthException
     * @throws DomainException
     */
    public function move(
        WorkflowRepositoryContract $workflowRepository,
        ActionPerformedByContract $performedBy,
        Stage $newStage
    ): void
    {
        if (!$performedBy->isMemberOf($this->companyAccount())) {
            throw DomainAuthException::notMemberOfCompany($this->companyAccount()->uuid());
        }

        self::checkPermission(
            new ActionPermissions\EditLead(),
            new ActionContext($performedBy, $this)
        );

        /* @var StageId $stageId */
        $stageId = $newStage->uuid();
        if (!$this->workflow()->findStage($stageId)) {
            throw DomainException::notFoundWithin('Stage', 'Workflow');
        }

        if ($this->stage->uuid()->equals($stageId)) {
            return;
        }

        $this->updatedAt = new \DateTimeImmutable();
        $this->setStage($newStage);
        $this->nextVersion();

        $workflowRepository->store($this->workflow());
    }

    /**
     * @return string
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * @return CompanyAccountContract
     */
    public function companyAccount(): CompanyAccountContract
    {
        return $this->companyAccount;
    }

    /**
     * @return Stage
     */
    public function stage(): Stage
    {
        return $this->stage;
    }

    /**
     * @return Workflow
     */
    public function workflow(): Workflow
    {
        return $this->stage->workflow();
    }

    /**
     * @return UserContract
     */
    public function owner(): UserContract
    {
        return $this->owner;
    }

    /**
     * @return UserContract
     */
    public function createdBy(): UserContract
    {
        return $this->createdBy;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function assignedAt(): \DateTimeImmutable
    {
        return $this->assignedAt;
    }


    /**
     * @return Revenue|null
     */
    public function estimatedRevenue(): ?Revenue
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
    public function plannedCloseAt(): \DateTimeImmutable
    {
        return $this->plannedCloseAt;
    }

    /**
     * @return \DateTimeImmutable|null
     */
    public function closedAt(): ?\DateTimeImmutable
    {
        return $this->closedAt;
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
     * @return \DateTimeImmutable
     */
    public function stageChangedAt(): \DateTimeImmutable
    {
        return $this->stageChangedAt;
    }

    /**
     * @inheritDoc
     */
    public function belongsToCompanyAccount(CompanyAccountContract $companyAccount): bool
    {
        return $this->companyAccount()->uuid()->equals(
            $companyAccount->uuid()
        );
    }

    /**
     * @return bool
     */
    public function isClosed(): bool
    {
        return $this->outcome() !== null;
    }

    /**
     * @param UserContract              $owner
     */
    private function setOwner(UserContract $owner): void
    {
        if (isset($this->owner) && $this->owner === $owner) {
            return;
        }
        $this->owner = $owner;
        $this->assignedAt = new \DateTimeImmutable();
    }

    /**
     * @param Stage $stage
     * @return $this
     */
    private function setStage(Stage $stage): self
    {
        $this->stage = $stage;
        $this->stageChangedAt = new \DateTimeImmutable();

        return $this;
    }
}
