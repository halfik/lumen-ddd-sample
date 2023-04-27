<?php

namespace Domains\Common\Models\Account;

interface UserCompanyAccountStatusContract
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_PENDING = 'pending';
    public const STATUS_INACTIVE = 'deactivated';
    public const STATUS_BLOCKED = 'blocked';

    public const ALL_STATUSES = [
        self::STATUS_ACTIVE,
        self::STATUS_PENDING,
        self::STATUS_INACTIVE,
        self::STATUS_BLOCKED,
    ];

    public const ALLOWED_TRANSITIONS = [
        self::STATUS_ACTIVE => [
            self::STATUS_INACTIVE,
            self::STATUS_BLOCKED,
        ],
        self::STATUS_PENDING => [
            self::STATUS_ACTIVE,
        ],
        self::STATUS_INACTIVE => [
            self::STATUS_ACTIVE,
        ],
        self::STATUS_BLOCKED => [
            self::STATUS_ACTIVE,
        ],
    ];
}
