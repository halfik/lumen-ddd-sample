<?php

namespace Infrastructure\Persistence\Doctrine\Types\Accounts;

use Domains\Accounts\Models\Company\UserCompanyAccountStatus;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;

class DoctrineUserCompanyAccountStatus extends TextType
{
    public const NAME = 'user_company_account_status';

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
     * @return string
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): string
    {
        return (string)$value;
    }

    /**
     * {@inheritdoc}
     * @return UserCompanyAccountStatus
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): UserCompanyAccountStatus
    {
        return new UserCompanyAccountStatus($value);
    }
}
