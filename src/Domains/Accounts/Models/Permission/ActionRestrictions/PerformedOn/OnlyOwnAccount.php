<?php

namespace Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;

use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionRestriction;

class OnlyOwnAccount extends ActionRestriction
{
    private const TYPE = 'performed_on_own_account';

    public function __construct()
    {
        parent::__construct(self::TYPE);
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
        if ($performedOn->user()->uuid()->equals($context->performedBy()->user()->uuid())) {
            return true;
        }
        return false;
    }
}

