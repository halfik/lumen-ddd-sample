<?php

namespace Domains\Common\Exceptions;

use Domains\Common\Models\Permission\Role;

class DomainException extends \Exception
{
    protected string $type;

    /**
     * @param $message
     * @param $type
     */
    protected function __construct($message, $type)
    {
        parent::__construct($message);
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    public static function accountAlreadyActive(): self
    {
        $msg = 'Account is already active.';
        $type ='account_already_active';

        return new self($msg, $type);
    }

    public static function accountStatusNotPending(): self
    {
        $msg = 'Account status has to be pending to perform action.';
        $type ='account_status_not_pending';

        return new self($msg, $type);
    }

    public static function accountStatusTransitionNowAllowed(): self
    {
        $msg = 'Account status change is not allowed.';
        $type ='account_status_transition_not_allowed';

        return new self($msg, $type);
    }

    /**
     * @return static
     */
    public static function companyAccountsInactive(): self
    {
        $msg = 'Company account is not active.';
        $type ='company_account_not_active';

        return new self($msg, $type);
    }

    public static function cantGrantRole(Role $role): self
    {
        $msg = sprintf('Role %s can not be granted', $role->name());
        $type ='cant_grant_role';

        return new self($msg, $type);
    }

    public static function notFound(string $model, string $attribute, string $attributeValue): self
    {
        $msg = sprintf('%s not found while using attribute %s: %s', $model, $attribute, $attributeValue);
        $type ='not_found';

        return new self($msg, $type);
    }

    public static function eventOnWrongModel(string $msg): self
    {
        return new self($msg, 'event_applied_on_wrong_model');
    }

    public static function required(string $msg): self
    {
        return new self($msg, 'required');
    }

    /**
     * @param string|null     $msg
     * @return static
     */
    public static function userInactive(string $msg = null): self
    {
        if(!$msg) {
            $msg = 'User company account not active';
        }
        $type = 'user_company_account_not_active';

        return new self($msg, $type);
    }

    /**
     * @param string $modelName
     * @param string $withinModel
     * @return static
     */
    public static function notFoundWithin(string $modelName, string $withinModel): self
    {
        $msg = sprintf("%s not found within %s", $modelName, $withinModel);
        $type = 'not_found_within';

        return new self($msg, $type);
    }

    /**
     * @param string $statusFrom
     * @param string $statusTo
     * @return static
     */
    public static function statusTransitionNotAllowed(string $statusFrom, string $statusTo): self
    {
        $msg = sprintf('Status transition not allowed %s -> %s ', $statusFrom, $statusTo);
        $type = 'status_transition_not_allowed';

        return new self($msg, $type);
    }

}
