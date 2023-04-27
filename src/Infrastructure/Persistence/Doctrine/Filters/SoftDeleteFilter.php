<?php

namespace Infrastructure\Persistence\Doctrine\Filters;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

/**
 * Filter to skip soft deleted entities
 */
class SoftDeleteFilter extends SQLFilter
{
    private const COLUMN = 'deleted_at';

    /**
     * @param ClassMetadata $targetEntity
     * @param string        $targetTableAlias
     * @return string
     * @throws \Doctrine\DBAL\Exception
     */
    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        $class = $targetEntity->getName();
        if (true === ($this->disabled[$class] ?? false)) {
            return '';
        }
        if (true === ($this->disabled[$targetEntity->rootEntityName] ?? false)) {
            return '';
        }

        if (!($targetEntity->fieldNames[self::COLUMN] ?? false)) {
            return '';
        }

        $platform = $this->getConnection()->getDatabasePlatform();
        return $platform->getIsNullExpression($targetTableAlias.'.'.self::COLUMN);
    }
}
