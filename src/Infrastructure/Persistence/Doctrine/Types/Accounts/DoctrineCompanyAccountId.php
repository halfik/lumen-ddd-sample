<?php

namespace Infrastructure\Persistence\Doctrine\Types\Accounts;

use Domains\Accounts\Models\Company\CompanyAccountId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;

class DoctrineCompanyAccountId extends GuidType
{
    public const NAME = 'company_account_id';

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
     * @return CompanyAccountId|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?CompanyAccountId
    {
        if (is_null($value)) {
            return null;
        }
        return new CompanyAccountId($value);
    }
}
