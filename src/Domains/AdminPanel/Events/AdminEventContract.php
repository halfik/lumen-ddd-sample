<?php

namespace Domains\AdminPanel\Events;

interface AdminEventContract
{
    public const ACTION_CREATED = 'created';
    public const ACTION_EDITED = 'edited';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_BLOCKED = 'blocked';
    public const ACTION_UNBLOCKED = 'unblocked';

    public const DOMAIN_ADMIN_ACCOUNTS = 'AdminAccounts';

    public const MODEL_USER = 'User';
    public const MODEL_USER_COMPANY_ACCOUNT  = 'UserCompanyAccount';
    public const MODEL_COMPANY_ACCOUNT = 'CompanyAccount';
}
