<?php

namespace Domains\Accounts\Models\Permission\ActionRestrictions\PerformedOn;

use Domains\Accounts\Models\Company\UserCompanyAccount;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Common\Models\Permission\ActionRestriction;

class LimitedToRoles extends ActionRestriction
{
    private const TYPE = 'limited_to_roles';
    private array $limitedToRoles;

    /**
     * @param array $limitedToRoles
     */

    public function __construct(array $limitedToRoles)
    {
        parent::__construct(self::TYPE);
        $this->limitedToRoles = $limitedToRoles;
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
        if ($context->performedBy()->uuid()->equals($performedOn->uuid())) {
            return true;
        }

        foreach ($this->limitedToRoles as $role) {
            if ($performedOn->role()->same($role)) {
                return true;
            }
        }

        return false;
    }
}

