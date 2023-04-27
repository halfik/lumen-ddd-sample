<?php

namespace Domains\Common\Events\Accounts;

use Domains\Common\Models\Permission\Role;

interface UserAccountEditedContract
{
    /**
     * @return Role|null
     */
    public function newRole(): ?Role;
}
