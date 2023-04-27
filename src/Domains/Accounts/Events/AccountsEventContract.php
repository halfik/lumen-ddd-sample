<?php

namespace Domains\Accounts\Events;

interface AccountsEventContract
{
    public const ACTION_ACTIVATED = 'activated';
    public const ACTION_CREATED = 'created';
    public const ACTION_DEACTIVATED = 'deactivated';
    public const ACTION_EDITED = 'edited';

    public const DOMAIN_ACCOUNTS = 'Accounts';

    public const MODEL_USER = 'User';
    public const MODEL_USER_COMPANY_ACCOUNT = 'UserCompanyAccount';
}
