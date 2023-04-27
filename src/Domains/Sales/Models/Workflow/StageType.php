<?php

namespace Domains\Sales\Models\Workflow;

use Domains\Common\Models\ValueObject;

class StageType implements ValueObject
{
    private const TYPE_NORMAL = 'NORMAL_STAGE';
    private const TYPE_CLOSURE = 'CLOSURE_STAGE';

    private const ALL_TYPES = [
        self::TYPE_CLOSURE,
        self::TYPE_NORMAL
    ];

    private string $type;

    /**
     * @param string $type
     */
    public function __construct(string $type)
    {
        if (!in_array($type, self::ALL_TYPES)) {
            $msg = sprintf("Type %s not supported", $type);
            throw new \InvalidArgumentException($msg);
        }
        $this->type = $type;
    }

    /**
     * @return static
     */
    public static function normal(): self
    {
        return new self(self::TYPE_NORMAL);
    }

    /**
     * @return static
     */
    public static function closure(): self
    {
        return new self(self::TYPE_CLOSURE);
    }

    /**
     * @param StageType $oType
     * @return bool
     */
    public function same(StageType $oType): bool
    {
        return $this->type() === $oType->type();
    }

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type(),
        ];
    }

    public static function fromRawData(array $data): static
    {
        return new self($data['type']);
    }

    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_HEX_TAG);
    }

    public function __toString(): string
    {
        return $this->type();
    }
}
