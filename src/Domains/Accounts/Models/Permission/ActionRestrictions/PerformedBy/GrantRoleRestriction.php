<?php

namespace Domains\Accounts\Models\Permission\ActionRestrictions\PerformedBy;

use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionRestriction;
use Domains\Common\Models\Permission\Role;

class GrantRoleRestriction extends ActionRestriction
{
    private const TYPE = 'performed_by_grant_role_restriction';

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

        /** @var Role|null $newRole */
        $newRole = $context->params()[0] ?? null;
        if ($newRole) {
            if($newRole->same(Role::companyOwner())) {
                return false;
            }

            if ($context->performedBy()->role()->hasPowerOver($newRole)) {
                return true;
            }
        }
        return false;
    }
}
