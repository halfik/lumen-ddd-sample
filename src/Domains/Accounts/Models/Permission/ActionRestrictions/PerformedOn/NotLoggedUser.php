<?php

namespace Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;

use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionRestriction;

class NotLoggedUser extends ActionRestriction
{
    private const TYPE = 'performed_on_not_logged_user';

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
            return true;
        }

        /** @var UserCompanyAccount $performedOn */
        $performedOn = $context->performedOn();

        if (!$performedOn->uuid()->equals($context->performedBy()->uuid())) {
            return true;
        }

        return false;
    }
}
