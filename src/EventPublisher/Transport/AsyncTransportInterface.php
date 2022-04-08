<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport;

use Neos\EventSourcing\SymfonyBridge\EventPublisher\SymfonyEventPublisher;

/**
 * Used by {@see SymfonyEventPublisher} to send events.
 */
interface AsyncTransportInterface
{
    public function send(
        string $listenerClassName,
        string $eventStoreContainerId
    ): void;
}
