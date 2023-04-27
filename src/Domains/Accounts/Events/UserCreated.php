<?php

namespace Domains\Accounts\Events;

use Domains\Common\Events\DomainEvent;
use Domains\Common\Models\Account\UserContract;

class UserCreated extends DomainEvent implements AccountsEventContract
{
    private const VERSION = 1;

    /**
     * @param UserContract $performedBy
     * @param UserContract $performedOn
     */
    public function __construct(private UserContract $performedBy, private UserContract $performedOn)
    {
    }


    /**
     * @return UserContract
     */
    public function user(): UserContract
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
        return self::MODEL_USER;
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
        $data['user_id'] = (string)$this->user()->uuid();

        return $data;
    }

    public function type(): string
    {
        return strtolower(sprintf('%s.%s.%s', $this->domain(), $this->model(), $this->action()));
    }
}
