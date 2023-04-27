<?php

namespace Domains\Common\Models;

use Doctrine\Common\Collections\Collection;

/**
 * It is required to change the model properties to `protected` to make them accessible from the trait
 */
trait ArrayableTrait
{
    /**
     * @var string[]
     */
    protected array $excludedProperties = [
        'uuid',
        'version',
        'updatedAt',
        'excludedProperties',
    ];

    /**
     * Converts the model into an array
     *
     * @return array
     */
    public function toArray(): array
    {
        $data = [];

        foreach ($this->properties() as $property) {
            $data[$property] = $this->resolveAttribute($this->$property);
        }

        foreach ($this->customFields() as $field => $definition) {
            $data[$field] = $definition($this, $field);
        }

        return $data;
    }

    /**
     * Provides the list of additional field names (as keys) and their definitions (as values)
     *
     * @return array
     */
    protected function customFields(): array
    {
        return [];
    }

    /**
     * Returns array with properties as keys and null as values
     *
     * @return array
     */
    public function skeleton(): array
    {
        $fields = array_merge($this->properties(), array_keys($this->customFields()));

        return array_fill_keys($fields, null);
    }

    /**
     * Provides the list of model properties
     *
     * @return array
     */
    private function properties(): array
    {
        $properties = array_keys(get_object_vars($this));

        return array_filter(
            $properties,
            fn(string $property) => !in_array($property, $this->excludedProperties, true)
        );
    }

    /**
     * Resolves attribute to a non-object value
     *
     * @param mixed $attribute
     * @param bool $secondDepth
     * @return mixed
     */
    private function resolveAttribute(mixed $attribute, bool $secondDepth = false): mixed
    {
        if ($attribute instanceof AggregateRoot) {
            return (string)$attribute->uuid();
        }

        if ($attribute instanceof ValueObject) {
            return $attribute->toArray();
        }

        if ($attribute instanceof \DateTimeInterface) {
            return $attribute->getTimestamp();
        }

        if (is_array($attribute) || $attribute instanceof Collection) {
            if ($secondDepth) {
                return [];
            }

            $items = [];
            foreach ($attribute as $key => $item) {
                $items[$key] = $this->resolveAttribute($item, true);
            }

            return $items;
        }

        if ($attribute instanceof \Stringable) {
            return (string)$attribute;
        }

        if (is_object($attribute) || is_resource($attribute)) {
            return 'Unhandled data type';
        }

        return $attribute;
    }
}
