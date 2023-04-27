<?php

namespace Infrastructure\Persistence\Doctrine\Types\Common;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use Domains\Common\Models\AggregateRootId;
use Domains\Common\Models\Dictionaries\IndustryId;

class DoctrineAggregateRootId extends GuidType
{
    public const NAME = 'generic_id';

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
        if(is_null($value)) {
            return null;
        }
        return (string)$value;
    }

    /**
     * {@inheritdoc}
     * @return IndustryId|Null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?AggregateRootId
    {
        if(is_null($value)) {
            return null;
        }
        return new AggregateRootId($value);
    }
}
