<?php

namespace Domains\Common\Models;

use Domains\Common\Exceptions\InvalidUUIDException;
use Ramsey\Uuid\Uuid;

class AggregateRootId
{
    /** @var string  */
    protected string $uuid;

    /**
     * AggregateRootId constructor.
     * @param null|string $uuid
     * @throws InvalidUUIDException
     */
    public function __construct(?string $uuid = null)
    {
        try {
            $this->uuid = Uuid::fromString($uuid ?: (string)Uuid::uuid4())->toString();
        } catch (\Exception $e) {
            throw new InvalidUUIDException($e);
        }
    }

    /**
     * Next id
     * @return static
     * @throws \Exception
     */
    public static function nextId(): static
    {
        return new static();
    }

    /**
     * @param AggregateRootId $aggregateRootId
     * @return bool
     */
    public function equals(AggregateRootId $aggregateRootId): bool
    {
        return $this->uuid === (string)$aggregateRootId;
    }

    /**
     * @return string
     */
    public function bytes(): string
    {
        return Uuid::fromString($this->uuid)->getBytes();
    }

    /**
     * @param string $bytes
     * @return AggregateRootId
     * @throws \Exception
     */
    public static function fromBytes(string $bytes): self
    {
        return new static(Uuid::fromBytes($bytes)->toString());
    }

    /**
     * @param string $uid
     * @return string
     * @throws \Exception
     */
    public static function toBytes(string $uid): string
    {
        return (new static($uid))->bytes();
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->uuid;
    }
}
