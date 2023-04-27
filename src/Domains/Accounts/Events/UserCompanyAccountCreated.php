<?php

namespace Domains\Accounts\Events;

use Domains\Common\Events\DomainEvent;
use Domains\Common\Models\Account\UserCompanyAccountContract;
use Domains\Common\Models\Permission\ActionPerformedByContract;

class UserCompanyAccountCreated extends DomainEvent implements AccountsEventContract
{
    private const VERSION = 1;

    /**
     * @param ActionPerformedByContract $performedBy
     * @param UserCompanyAccountContract $performedOn
     */
    public function __construct(private ActionPerformedByContract $performedBy, private UserCompanyAccountContract $performedOn)
    {
    }

    /**
     * @return UserCompanyAccountContract
     */
    public function userCompanyAccount(): UserCompanyAccountContract
    {
        return $this->performedOn;
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
        return self::ACTION_CREATED;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return self::VERSION;
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $data = parent::toArray();

        $data['id'] = (string)$this->userCompanyAccount()->uuid();
        $data['company_account_id'] = (string)$this->userCompanyAccount()->companyAccount()->uuid();
        $data['user_id'] = (string)$this->userCompanyAccount()->user()->uuid();

        return $data;
    }

    public function type(): string
    {
        return strtolower(sprintf('%s.%s.%s', $this->domain(), $this->model(), $this->action()));
    }
}
