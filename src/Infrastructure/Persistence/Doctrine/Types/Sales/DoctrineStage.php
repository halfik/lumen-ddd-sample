<?php

namespace Infrastructure\Persistence\Doctrine\Types\Sales;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\TextType;
use Domains\Sales\Models\Workflow\StageType;

class DoctrineStage extends TextType
{
    public const NAME = 'stage_type';

    /**
     * {@inheritdoc}
     * @return string
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * @param StageType|null $value
     * @param AbstractPlatform $platform
     * @return string|null
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (is_null($value)) {
            return null;
        }
        return $value->type();
    }

    /**
     * {@inheritdoc}
     * @return StageType
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): StageType
    {
        return new StageType($value);
    }
}
