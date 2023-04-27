<?php

namespace Infrastructure\Persistence\Doctrine\Types\Accounts;

use Domains\Accounts\Models\User\Password;
use Doctrine\DBAL\Types\TextType;
use Doctrine\DBAL\Platforms\AbstractPlatform;

class DoctrinePassword extends TextType
{
    public const NAME = 'password';

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
     * @return Password
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): Password
    {
        return new Password($value);
    }
}
