<?php

namespace Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;

use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionRestriction;

class OnlyWhenAccountStatus extends ActionRestriction
{
    private const TYPE = 'performed_on_account_with_status';
    /** @var UserCompanyAccountStatus[]  */
    private array $allowedStatuses;

    /**
     * @param UserCompanyAccountStatus[] $allowedStatuses
     */
    public function __construct(array $allowedStatuses)
    {
        parent::__construct(self::TYPE);
        $this->allowedStatuses = $allowedStatuses;
    }

    /**
     * @inheritDoc
     * @throws \Exception
     */
    public function pass(ActionContext $context): bool
    {
        if (!$context->performedOn() instanceof UserCompanyAccount) {
            return false;
        }

        /** @var UserCompanyAccount $performedOn */
        $performedOn = $context->performedOn();
        if ($this->isAllowed($performedOn->status())) {
            return true;
        }
        return false;
    }

    /**
     * Check if given status is allowed
     * @param UserCompanyAccountStatus $status
     * @return bool
     */
    private function isAllowed(UserCompanyAccountStatus $status): bool
    {
        foreach ($this->allowedStatuses as $aStatus) {
            if ($aStatus->same($status)) {
                return true;
            }
        }
        return false;
    }
}

