<?php

namespace Domains\AdminPanel\Events\Accounts;

use Domains\AdminPanel\Events\AdminEventContract;
use Domains\AdminPanel\Models\Accounts\CompanyAccount;
use Domains\Common\Events\DomainEvent;

class CompanyAccountUnblocked extends DomainEvent implements AdminEventContract
{
    private const VERSION = 1;

    /**
     * @param CompanyAccount $performedOn
     */
    public function __construct(private CompanyAccount $performedOn)
    {
    }

    /**
     * @return CompanyAccount
     */
    public function performedOn(): CompanyAccount
    {
        return $this->performedOn;
    }

    /**
     * @return string
     */
    public function domain(): string
    {
        return self::DOMAIN_ADMIN_ACCOUNTS;
    }

    /**
     * @return string
     */
    public function model(): string
    {
        return self::MODEL_COMPANY_ACCOUNT;
    }

    /**
     * @return string
     */
    public function action(): string
    {
        return self::ACTION_UNBLOCKED;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return self::VERSION;
    }

    /**
     * @return array
     */
    public function toArray(): array
    {
        $data = parent::toArray();
        $data['user_id'] = (string)$this->performedOn()->uuid();

        return $data;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return strtolower(sprintf('%s.%s.%s', $this->domain(), $this->model(), $this->action()));
    }
}

