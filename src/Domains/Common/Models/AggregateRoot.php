<?php

namespace Domains\Common\Models;

use Domains\Common\Models\Permission\ActionPerformedOnContract;
use Domains\Common\Models\Permission\CheckActionPermissionTrait;

abstract class AggregateRoot implements ActionPerformedOnContract
{
    use CheckActionPermissionTrait;
    use ArrayableTrait;

    /** @var AggregateRootId  */
    protected AggregateRootId $uuid;
    /** @var int */
    protected int $version;

    /**
     * AggregateRoot constructor.
     * @param AggregateRootId $id
     * @param int $version
     */
    protected function __construct(AggregateRootId $id, int $version = 1)
    {
        $this->uuid = $id;
        $this->version = $version;
    }

    /**
     * @return int
     */
    public function version(): int
    {
        return $this->version;
    }

    /**
     * @return $this
     */
    public function nextVersion(): AggregateRoot
    {
        $this->version++;
        return $this;
    }

    /**
     * @return mixed
     */
    public function uuid(): AggregateRootId
    {
        return $this->uuid;
    }

    /**
     * @param AggregateRoot $aggregateRoot
     * @return bool
     */
    final public function same(AggregateRoot $aggregateRoot): bool
    {
        return $this->uuid->equals($aggregateRoot->uuid());
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return (string)$this->uuid;
    }
}
