<?php

namespace Infrastructure\Persistence\Doctrine\Types\Common;

use Domains\Common\Models\Permission\Role;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;

class DoctrineRole extends GuidType
{
    public const NAME = 'role';

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
     * @return Role
     * @throws \Exception
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): Role
    {
        return Role::fromName($value);
    }
}

