<?php

namespace Infrastructure\Persistence\Doctrine\Types\Sales;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use Domains\Sales\Models\Revenue;

class DoctrineRevenue extends IntegerType
{
    public const NAME = 'revenue';

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @param Revenue|null $value
     * @param AbstractPlatform $platform
     * @return int|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if (is_null($value)) {
            return null;
        }
        return $value->value();
    }

    /**
     * {@inheritdoc}
     * @return Revenue|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Revenue
    {
        if (is_null($value)) {
            return null;
        }
        return new Revenue($value);
    }
}
