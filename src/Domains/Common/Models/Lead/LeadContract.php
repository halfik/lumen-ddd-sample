<?php

namespace Domains\Common\Models\Lead;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\ActionPerformedOnContract;
use Domains\Sales\Models\Revenue;
use Domains\Sales\Models\Workflow\Stage;
use Domains\Sales\Models\Workflow\Workflow;

interface LeadContract extends ActionPerformedOnContract
{
    /**
     * @return mixed
     */
    public function uuid(): AggregateRootId;

    /**
     * @param CompanyAccountContract $companyAccount
     * @return bool
     */
    public function belongsToCompanyAccount(CompanyAccountContract $companyAccount): bool;

    /**
     * @return string
     */
    public function title(): string;

    /**
     * @return CompanyAccountContract
     */
    public function companyAccount(): CompanyAccountContract;

    /**
     * @return Stage
     */
    public function stage(): Stage;

    /**
     * @return Workflow
     */
    public function workflow(): Workflow;

    /**
     * @return UserContract
     */
    public function owner(): UserContract;

    /**
     * @return UserContract
     */
    public function createdBy(): UserContract;


    /**
     * @return Revenue|null
     */
    public function estimatedRevenue(): ?Revenue;

    /**
     * @return Revenue
     */
    public function actualRevenue(): Revenue;

    /**
     * @return \DateTimeImmutable
     */
    public function plannedCloseAt(): \DateTimeImmutable;

    /**
     * @return \DateTimeImmutable|null
     */
    public function closedAt(): ?\DateTimeImmutable;

    /**
     * @return \DateTimeImmutable
     */
    public function createdAt(): \DateTimeImmutable;

    /**
     * @return \DateTimeImmutable
     */
    public function updatedAt(): \DateTimeImmutable;

    /**
     * @return \DateTimeImmutable
     */
    public function assignedAt(): \DateTimeImmutable;
}
