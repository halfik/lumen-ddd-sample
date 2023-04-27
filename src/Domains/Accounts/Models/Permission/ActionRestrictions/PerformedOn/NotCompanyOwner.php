<?php

namespace Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;

use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionRestriction;
use Domains\Common\Models\Permission\Role;

class NotCompanyOwner extends ActionRestriction
{
    private const TYPE = 'performed_on_not_company_owner';

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

        if (!$performedOn->role()->same(Role::companyOwner())) {
            return true;
        }

        return false;
    }
}

