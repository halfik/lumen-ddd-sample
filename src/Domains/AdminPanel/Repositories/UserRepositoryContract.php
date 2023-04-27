<?php

namespace Domains\AdminPanel\Repositories;

use Domains\AdminPanel\Models\Accounts\User;
use Domains\Common\Models\AggregateRootId;

interface UserRepositoryContract
{
    /**
     * Find user by given email
     * If $editedUserId is given. That user is ignored by query.
     *
     * @param string $email
     * @param null|AggregateRootId $editedUserId
     * @return User|null
     */
    public function findOneByEmail(string $email, ?AggregateRootId $editedUserId): ?User;

    /**
     * Find user by id
     *
     * @param AggregateRootId $userId
     * @return User|null
     */
    public function findById(AggregateRootId $userId): ?User;

    /**
     * Create or update given user
     * @param User $model
     * @return $this
     */
    public function store(User $model): self;

    /**
     * Flush changes to data storage
     * @return $this
     */
    public function flush(): self;
}
