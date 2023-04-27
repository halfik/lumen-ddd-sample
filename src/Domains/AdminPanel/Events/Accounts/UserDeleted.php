<?php

namespace Domains\AdminPanel\Events\Accounts;

use Domains\AdminPanel\Events\AdminEventContract;
use Domains\AdminPanel\Models\Accounts\User;
use Domains\Common\Events\DomainEvent;

class UserDeleted extends DomainEvent implements AdminEventContract
{
    private const VERSION = 1;

    /**
     * @param User      $performedOn
     */
    public function __construct(private User $performedOn) {}

    /**
     * @return User
     */
    public function performedOn(): User
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
        return self::MODEL_USER;
    }

    /**
     * @return string
     */
    public function action(): string
    {
        return self::ACTION_DELETED;
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
