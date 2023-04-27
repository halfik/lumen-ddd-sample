<?php

namespace Domains\Accounts\Events;

use Domains\Common\Events\DomainEvent;
use Domains\Common\Models\Account\UserCompanyAccountContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\ActionPerformedByContract;

class UserCompanyAccountDeactivated extends DomainEvent implements AccountsEventContract
{
    private const VERSION = 1;

    /**
     * @param ActionPerformedByContract $performedBy
     * @param UserCompanyAccountContract $performedOn
     * @param array $oldValues
     */
    public function __construct(
        private ActionPerformedByContract $performedBy,
        private UserCompanyAccountContract $performedOn,
        private array $oldValues
    ) {
    }


    /**
     * @inheritDoc
     */
    public function performedOnId(): AggregateRootId
    {
        return $this->performedOn->uuid();
    }

    /**
     * @inheritDoc
     */
    public function domain(): string
    {
        return self::DOMAIN_ACCOUNTS;
    }

    /**
     * @inheritDoc
     */
    public function model(): string
    {
        return self::MODEL_USER_COMPANY_ACCOUNT;
    }

    /**
     * @inheritDoc
     */
    public function action(): string
    {
        return self::ACTION_DEACTIVATED;
    }

    /**
     * @inheritDoc
     */
    public function version(): int
    {
        return self::VERSION;
    }

    /**
     * @return AggregateRootId
     */
    public function companyAccountId(): AggregateRootId
    {
        return $this->performedOn->companyAccount()->uuid();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['company_account_id'] = (string)$this->companyAccountId();
        $data['user_id'] = (string)$this->performedOnId();

        return $data;
    }

    public function type(): string
    {
        return strtolower(sprintf('%s.%s.%s', $this->domain(), $this->model(), $this->action()));
    }
}
