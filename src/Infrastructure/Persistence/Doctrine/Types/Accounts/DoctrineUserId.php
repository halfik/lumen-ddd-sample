<?php

namespace Infrastructure\Persistence\Doctrine\Types\Accounts;

use Domains\Accounts\Models\User\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;

class DoctrineUserId extends GuidType
{
    public const NAME = 'user_id';

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
     * @return UserId|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserId
    {
        if (is_null($value)) {
            return null;
        }
        return new UserId($value);
    }
}

