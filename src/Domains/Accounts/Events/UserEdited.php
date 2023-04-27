<?php

namespace Domains\Accounts\Events;

use Domains\Common\Events\Accounts\UserAccountEditedContract;
use Domains\Common\Events\DomainEvent;
use Domains\Common\Models\Account\UserContract;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\ActionPerformedByContract;
use Domains\Common\Models\Permission\Role;

class UserEdited extends DomainEvent implements
    AccountsEventContract,
    UserAccountEditedContract
{
    private const VERSION = 1;

    /**
     * @param ActionPerformedByContract $performedBy
     * @param UserContract              $performedOn
     * @param array                     $oldValues
     * @param Role|null                 $newRole
     */
    public function __construct(
        private ActionPerformedByContract $performedBy,
        private UserContract $performedOn,
        private array $oldValues,
        private ?Role $newRole = null,
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
     * @return Role|null
     */
    public function newRole(): ?Role
    {
        return $this->newRole;
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
        return self::ACTION_EDITED;
    }

    /**
     * @inheritDoc
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
        $data['user_id'] = (string)$this->performedOnId();

        return $data;
    }

    public function type(): string
    {
        return strtolower(sprintf('%s.%s.%s', $this->domain(), $this->model(), $this->action()));
    }
}

