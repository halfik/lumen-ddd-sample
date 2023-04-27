<?php

namespace Domains\Sales\Validation;

use Domains\Common\Models\Account\CompanyAccountContract;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Common\Validation\Validator;
use Domains\Sales\Models\Workflow\Workflow;

class LeadValidator extends Validator
{
    private const TITLE_MIN = 2;
    private const TITLE_MAX = 255;

    private Workflow $workflow;

    /**
     * @param Workflow               $workflow
     */
    public function __construct(Workflow $workflow)
    {
        parent::__construct();
        $this->workflow = $workflow;
    }

    /**
     * @return CompanyAccountContract
     */
    private function companyAccount(): CompanyAccountContract
    {
        return $this->workflow->companyAccount();
    }

    /**
     * @param string $value
     * @return $this
     */
    public function title(string $value): self
    {
        if (strlen($value) < self::TITLE_MIN) {
            $this->exception()->addError('title', 'min', [self::TITLE_MIN]);
        }
        if (strlen($value) > self::TITLE_MAX) {
            $this->exception()->addError('title', 'max', [self::TITLE_MAX]);
        }

        return $this;
    }


    /**
     * @param ActionPerformedByContract $createdBy
     * @return $this
     */
    public function createdBy(ActionPerformedByContract $createdBy): self
    {
        if(!$createdBy->isMemberOf($this->companyAccount())) {
            $this->exception()->addError('created_by', 'not_member', [(string)$this->companyAccount()->uuid()]);
        }
        return $this;
    }

    /**
     * @param UserContract $user
     * @return $this
     */
    public function owner(UserContract $user): self
    {
        if(!$this->companyAccount()->hasMember($user->uuid(), true)) {
            $this->exception()->addError('owner_id', 'not_member', [(string)$this->companyAccount()->uuid()]);
        }
        return $this;
    }

    /**
     * @param \DateTimeImmutable $date
     * @return $this
     * @throws \Exception
     */
    public function plannedCloseAt(\DateTimeImmutable $date): self
    {
        if ($date->getTimestamp() < (new \DateTimeImmutable(date('Y-m-d 00:00:00')))->getTimestamp()) {
            $this->exception()->addError('planned_close_at', 'date_in_past');
        }
        return $this;
    }

    /**
     * @param \DateTimeImmutable $date
     * @return $this
     */
    public function closeAt(\DateTimeImmutable $date): self
    {
        if($date->getTimestamp() > time()) {
            $this->exception()->addError('closed_at', 'date_in_future');
        }
        return $this;
    }
}
