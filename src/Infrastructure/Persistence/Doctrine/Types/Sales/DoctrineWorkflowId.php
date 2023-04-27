<?php

namespace Infrastructure\Persistence\Doctrine\Types\Sales;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use Domains\Sales\Models\Workflow\WorkflowId;

class DoctrineWorkflowId extends GuidType
{
    public const NAME = 'workflow_id';

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
     * @return WorkflowId
     */
    public function convertToPHPValue($value, AbstractPlatform $platform): WorkflowId
    {
        return new WorkflowId($value);
    }
}
