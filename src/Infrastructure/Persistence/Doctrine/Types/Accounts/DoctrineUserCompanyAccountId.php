<?php

namespace Infrastructure\Persistence\Doctrine\Types\Accounts;

use Domains\Accounts\Models\Company\UserCompanyAccountId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;

class DoctrineUserCompanyAccountId extends GuidType
{
    public const NAME = 'user_company_account_id';

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * {@inheritdoc}
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (is_null($value)) {
            return null;
        }
        return (string)$value;
    }

    /**
     * {@inheritdoc}
     * @return UserCompanyAccountId|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform):? UserCompanyAccountId
    {
        if (is_null($value)) {
            return null;
        }
        return new UserCompanyAccountId($value);
    }
}

