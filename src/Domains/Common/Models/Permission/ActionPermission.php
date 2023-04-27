<?php

namespace Domains\Common\Models\Permission;

use Domains\Common\Models\ValueObject;

abstract class ActionPermission implements ValueObject, PermissionsContract
{
    private string $name;
    private array $restrictions;
    /** @var bool action type: view only */
    private bool $viewType;

    /**
     * @param string $name
     * @param array  $restrictions
     * @param bool   $viewType
     */
    public function __construct(
        string $name,
        array $restrictions = [],
        bool $viewType = false
    )
    {
        if (!in_array($name, self::ALL_PERMISSIONS)) {
            throw new \InvalidArgumentException('Unsupported permission.');
        }
        $this->name = $name;
        $this->restrictions = $restrictions;
        $this->viewType = $viewType;
    }

    /**
     * @return string
     */
    public function name(): string
    {
        return $this->name;
    }

    /**
     * @return ActionRestriction[]
     */
    public function restrictions(): array
    {
        return $this->restrictions;
    }

    /**
     * @return bool
     */
    public function isViewOnlyType(): bool
    {
        return $this->viewType;
    }

    /**
     * @param ActionPermission $oPermission
     * @return bool
     */
    public function same(ActionPermission $oPermission): bool
    {
        return $this->name() === $oPermission->name();
    }

    /**
     * @inheritDoc
     */
    public function toArray(): array
    {
        $restrictions = [];
        foreach ($this->restrictions() as $restriction) {
            $restrictions[] = $restriction->toArray();
        }

        return [
            'name' => $this->name(),
            'restrictions' => $restrictions,
        ];
    }

    /**
     * @inheritDoc
     */
    public static function fromRawData(array $data): static
    {
        $restrictions = [];
        foreach ($data['restrictions'] as $restrictionsData) {
            $restrictions[] = ActionRestriction::fromRawData($restrictionsData);
        }

        return new static($data['name'], $restrictions);
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
        return $this->name();
    }
}
