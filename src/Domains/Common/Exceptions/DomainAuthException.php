<?php

namespace Domains\Common\Exceptions;

use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Permission\PermissionsContract;

/**
 * Exception used in domain for authentication error
 */
class DomainAuthException extends DomainException
{
    /**
     * @param PermissionsContract $permission
     * @return static
     */
    public static function fromPermission(PermissionsContract $permission): self
    {
        $msg = sprintf('Action not allowed: %s', $permission->name());
        $type = 'forbidden_action';
        return new self($msg, $type);
    }

    /**
     * @param AggregateRootId      $companyAccountId
     * @param AggregateRootId|null $userId
     * @return static
     */
    public static function notMemberOfCompany(AggregateRootId $companyAccountId, AggregateRootId $userId=null): self
    {
        $msg = sprintf('You are not a member of company %s', (string)$companyAccountId);
        if ($userId) {
            $msg = sprintf(
                'User %s is not a member of company %s',
                (string)$userId,
                (string)$companyAccountId
            );
        }

        $type = 'user_not_member_of_company';
        return new self($msg, $type);
    }
}
