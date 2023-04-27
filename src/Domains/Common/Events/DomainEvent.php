<?php

namespace Domains\Common\Events;

abstract class DomainEvent
{
    /**
     * Provider event type name
     * @return string
     */
    abstract public function type(): string;

    /**
     * Version is needed in case of any feature changes on event
     * So any service that is interested on event can distinguish events they know how to handle from new ones
     * @return int
     */
    abstract public function version(): int;

    /**
     * Translate event into queue message
     * @return array
     */
    public function toArray(): array
    {
        return [
            'type' => $this->type(),
            'version' => $this->version(),
        ];
    }
}
