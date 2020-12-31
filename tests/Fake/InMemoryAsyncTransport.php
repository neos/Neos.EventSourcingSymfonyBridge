<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Fake;

use Neos\EventSourcing\SymfonyBridge\Transport\AsyncTransportInterface;

class InMemoryAsyncTransport implements AsyncTransportInterface
{
    protected $dispatchedEvents;

    public function send(
        string $listenerClassName,
        string $eventStoreContainerId
    ): void
    {
        $this->dispatchedEvents[] = new class (
            $listenerClassName,
            $eventStoreContainerId
        ) {
            protected $listenerClassName;
            protected $eventStoreContainerId;

            public function __construct(
                string $listenerClassName,
                string $eventStoreContainerId
            )
            {
                $this->listenerClassName = $listenerClassName;
                $this->eventStoreContainerId = $eventStoreContainerId;
            }

            public function listenerClassName(): string
            {
                return $this->listenerClassName;
            }

            public function eventStoreContainerId(): string
            {
                return $this->eventStoreContainerId;
            }
        };
    }

    public function dispatchedEvents()
    {
        return $this->dispatchedEvents;
    }

    public function firstDispatchedEvent()
    {
        return current($this->dispatchedEvents);
    }
}
