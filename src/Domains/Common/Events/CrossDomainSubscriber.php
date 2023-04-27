<?php

namespace Domains\Common\Events;

/**
 * Subscriber that allows event driven communication between different bounded contexts
 */
class CrossDomainSubscriber
{
    private array $listen = [
    ];

    /**
     * Handle even passed from any domain
     * @param DomainEvent $event
     */
    public function handleEvent(DomainEvent $event): void
    {
        $listeners = $this->getListeners($event);
        if (!empty($listeners)) {
            foreach ($listeners as $listener) {
                $tmp = explode('@', $listener);
                $subscriber = new $tmp[0]();
                $subscriber->{$tmp[1]}($event);
            }
        }
    }

    /**
     * @param DomainEvent $event
     * @return array
     */
    private function getListeners(DomainEvent $event): array
    {
        $eventNames[] = get_class($event);
        $eventNames = array_merge($eventNames, array_values(class_implements($event)));

        $listeners = [];
        foreach ($eventNames as $eventName) {
            if ($eventListeners = ($this->listen[$eventName] ?? null)) {
                $listeners[] = $eventListeners;
            }
        }

        return array_merge([], ...$listeners);
    }
}
