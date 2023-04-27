<?php

namespace Domains\Sales\Models;

use Domains\Common\Models\ValueObject;

class Revenue implements ValueObject
{
    public const MAX_VALUE = 99999999999999;
    private int $value;

    /**
     * @param int $value
     */
    public function __construct(int $value = 0)
    {
        if ($value < 0) {
            throw new \InvalidArgumentException('Revenue can not be negative');
        }
        $this->value = $value;
    }

    /**
     * @param float $value
     * @return static
     */
    public static function fromDecimal(float $value): self
    {
        if($value > self::MAX_VALUE) {
            $msg = sprintf('Max value: %s', self::MAX_VALUE);
            throw new \InvalidArgumentException($msg);
        }
        $value *= 100;
        return new self(round($value));
    }

    /**
     * @param bool $convertToFloat
     * @return int|float
     */
    public function value(bool $convertToFloat = false): int|float
    {
        if ($convertToFloat) {
            return round($this->value/100, 2);
        }
        return $this->value;
    }

    public function add(int $add): self
    {
        $this->value += $add;
        return $this;
    }

    public function sub(int $sub): self
    {
        $this->value -= $sub;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'value' => $this->value()
        ];
    }

    public static function fromRawData(array $data): static
    {
        return new self($data['value']);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_HEX_TAG);
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
