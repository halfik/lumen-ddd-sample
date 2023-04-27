<?php

namespace Infrastructure\Persistence\Doctrine\Types\Sales;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use Domains\Sales\Models\Lead\LeadId;

class DoctrineLeadId extends GuidType
{
    public const NAME = 'lead_id';

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
     * @return LeadId|null
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): ?LeadId
    {
        if (is_null($value)) {
            return null;
        }
        return new LeadId($value);
    }
}
