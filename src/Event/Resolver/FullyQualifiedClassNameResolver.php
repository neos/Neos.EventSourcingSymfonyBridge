<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Event\Resolver;

use Neos\EventSourcing\Event\DomainEventInterface;
use Neos\EventSourcing\Event\EventTypeResolverInterface;

class FullyQualifiedClassNameResolver implements EventTypeResolverInterface
{
    public function getEventType(DomainEventInterface $event): string
    {
        return get_class($event);
    }

    public function getEventClassNameByType(string $eventType): string
    {
        return $eventType;
    }
}
