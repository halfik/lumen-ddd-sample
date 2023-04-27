<?php

namespace Domains\Common\Models\Account;

use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Accounts\Models\User\UserId;
use Domains\Common\Events\WorkflowCreatedContract;
use Domains\Common\Models\AggregateRoot;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\ActionPerformedOnContract;
use Domains\Common\Models\WorkflowContract;

interface CompanyAccountContract extends ActionPerformedOnContract
{
    public const SYSTEM_ENTITY_ID = '3f2fba14-9599-4bd8-a4fa-01f831e84929';

    /**
     * Check if company has a member
     * @param AggregateRootId $userId
     * @param bool   $onlyActive
     * @return bool
     */
    public function hasMember(AggregateRootId $userId, bool $onlyActive = false): bool;

    /**
     * @param UserId|AggregateRootId $userId
     * @return UserCompanyAccount|null
     */
    public function findMember(AggregateRootId|UserId $userId): ?UserCompanyAccount;

    /**
     * @return bool
     */
    public function isActive(): bool;

    /**
     * @return WorkflowContract
     */
    public function defaultWorkflow(): WorkflowContract;

    /**
     * @param AggregateRoot $aggregateRoot
     * @return bool
     */
    public function same(AggregateRoot $aggregateRoot): bool;

    /**
     * @param WorkflowCreatedContract $event
     */
    public function applyWorkflowCreated(WorkflowCreatedContract $event): void;
}
