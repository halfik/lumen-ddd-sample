<?php

namespace Domains\Common\Models\Permission;

interface PermissionsContract
{
    public const PERMISSIONS_GROUP_USER = 'user';
    public const PERMISSIONS_GROUP_LEAD = 'lead';
    public const PERMISSIONS_GROUP_WORKFLOW = 'workflow';

    public const ALL_PERMISSIONS = [
        self::COMPANY_ACCOUNT_LOGIN_AS_USER,
        self::COMPANY_ACCOUNT_LIST_USERS,
        self::COMPANY_ACCOUNT_VIEW_USER,
        self::COMPANY_ACCOUNT_EDIT_USER,
        self::COMPANY_ACCOUNT_ACTIVATE_USER,
        self::COMPANY_ACCOUNT_DEACTIVATE_USER,

        self::USER_CHANGE_PASSWORD,

        self::LEAD_CREATE,
        self::LEAD_DELETE,
        self::LEAD_UPDATE,
        self::LEAD_CLOSE,
        self::LEAD_VIEW,

        self::STAGE_CREATE,
        self::STAGE_MOVE,

        self::WORKFLOW_VIEW,
    ];

    public const COMPANY_ACCOUNT_LOGIN_AS_USER = 'login-as-company-account-user';
    public const COMPANY_ACCOUNT_LIST_USERS = 'list-company-account-users';
    public const COMPANY_ACCOUNT_VIEW_USER = 'view-company-account-user-details';
    public const COMPANY_ACCOUNT_EDIT_USER = 'edit-company-account-user';
    public const COMPANY_ACCOUNT_CHANGE_USER_ROLE = 'change-company-account-user-role';
    public const COMPANY_ACCOUNT_ACTIVATE_USER = 'activate-company-account-user';
    public const COMPANY_ACCOUNT_DEACTIVATE_USER = 'deactivate-company-account-user';

    public const USER_CHANGE_PASSWORD = 'change-password-user';

    public const LEAD_CREATE = 'create-lead';
    public const LEAD_DELETE = 'delete-lead';
    public const LEAD_UPDATE = 'update-lead';
    public const LEAD_CLOSE = 'close-lead';
    public const LEAD_VIEW = 'view-lead';

    public const STAGE_CREATE = 'create-stage';
    public const STAGE_UPDATE = 'update-stage';
    public const STAGE_DELETE = 'delete-stage';
    public const STAGE_MOVE = 'move-stage';

    public const WORKFLOW_VIEW = 'view-workflow';

    public function name(): string;
}
