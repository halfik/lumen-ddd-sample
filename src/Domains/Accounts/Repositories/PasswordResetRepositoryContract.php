<?php

namespace Domains\Accounts\Repositories;

use Domains\Accounts\Models\User\PasswordReset;
use Domains\Accounts\Models\User\PasswordResetId;

interface PasswordResetRepositoryContract
{
    /**
     * @param PasswordResetId $id
     * @return PasswordReset|null
     */
    public function findById(PasswordResetId $id): ?PasswordReset;

    /**
     * Create or update password reset model
     * @param PasswordReset $model
     * @return $this
     */
    public function store(PasswordReset $model): self;

    /**
     * Remove given password reset from data storage
     * @param PasswordReset $model
     * @return $this
     */
    public function remove(PasswordReset $model): self;

    /**
     * Flush changes to data storage
     * @return $this
     */
    public function flush(): self;
}
