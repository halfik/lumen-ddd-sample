<?php

namespace Domains\Common\Models;

interface ValueObject
{
    /**
     * @return array
     */
    public function toArray(): array;

    /**
     * @param array $data
     * @return static
     */
    public static function fromRawData(array $data): static;

    /**
     * @return string
     */
    public function toJson(): string;

    /**
     * @return string
     */
    public function __toString(): string;
}
