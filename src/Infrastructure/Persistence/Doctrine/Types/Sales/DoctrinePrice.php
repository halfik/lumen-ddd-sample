<?php

namespace Infrastructure\Persistence\Doctrine\Types\Sales;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\IntegerType;
use Domains\Sales\Models\Product\Price;

class DoctrinePrice extends IntegerType
{
    public const NAME = 'price';

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @param Price|null $value
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
     * @return Price|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?Price
    {
        if (is_null($value)) {
            return null;
        }
        return new Price($value);
    }
}
