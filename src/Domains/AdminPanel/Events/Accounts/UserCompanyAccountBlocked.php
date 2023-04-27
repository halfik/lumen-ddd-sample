<?php

namespace Domains\AdminPanel\Events\Accounts;

use Domains\AdminPanel\Events\AdminEventContract;
use Domains\Common\Events\Accounts\UserCompanyAccountBlockedContract;
use Domains\Common\Events\DomainEvent;
use Domains\Common\Models\AggregateRootId;

class UserCompanyAccountBlocked extends DomainEvent implements
    AdminEventContract,
    UserCompanyAccountBlockedContract
{
    private const VERSION = 1;

    /**
     * @param AggregateRootId $userId
     * @param AggregateRootId $companyAccountId
     */
    public function __construct(private AggregateRootId $userId, private AggregateRootId $companyAccountId)
    {
    }

    public function userId(): AggregateRootId
    {
        return $this->userId;
    }

    public function companyAccountId(): AggregateRootId
    {
        return $this->companyAccountId;
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
        return self::MODEL_USER_COMPANY_ACCOUNT;
    }

    /**
     * @return string
     */
    public function action(): string
    {
        return self::ACTION_BLOCKED;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return self::VERSION;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return strtolower(sprintf('%s.%s.%s', $this->domain(), $this->model(), $this->action()));
    }
}
