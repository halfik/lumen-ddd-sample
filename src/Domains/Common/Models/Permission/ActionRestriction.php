<?php

namespace Domains\Common\Models\Permission;

use Domains\Common\Models\ValueObject;

abstract class ActionRestriction implements ValueObject
{
    protected string $type;

    /**
     * @param string $type
     */
    protected function __construct(string $type)
    {
        $this->type = $type;
    }

    /**
     * Check if action context passes by given restriction
     * @param ActionContext $context
     * @return bool
     */
    abstract public function pass(ActionContext $context): bool;

    /**
     * @return string
     */
    public function type(): string
    {
        return $this->type;
    }


    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type(),
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromRawData(array $data): static
    {
        return new static($data['type']);
    }

    /**
     * @inheritDoc
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_HEX_TAG);
    }

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        return $this->type();
    }
}
