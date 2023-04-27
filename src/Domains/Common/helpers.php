<?php

use Domains\Common\Events;

if(!function_exists('domainEvent')) {
    function domainEvent(Events\DomainEvent $event) {
        $subscriber = new Events\CrossDomainSubscriber();
        $subscriber->handleEvent($event);
    }
}
