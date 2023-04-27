<?php

namespace Domains\Sales\Models\Permissions\ActionRestrictions\PerformedOn;

use Domains\Common\Models\Permission\ActionRestriction;
use Domains\Common\Models\Permission\ActionContext;
use Domains\Sales\Models\Lead\Lead;

class OnlyOwnLead extends ActionRestriction
{
    private const TYPE = 'performed_on_own_lead';

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
        if (!$context->performedOn() instanceof Lead) {
            return false;
        }

        /** @var Lead $performedOn */
        $performedOn = $context->performedOn();
        if ($performedOn->owner()->uuid()->equals($context->performedBy()->user()->uuid())) {
            return true;
        }
        return false;
    }
}
