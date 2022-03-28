<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Messenger\Handler;

use Neos\EventSourcing\EventListener\EventListenerInvoker;
use Neos\EventSourcing\SymfonyBridge\Transport\MessengerTransport;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * This message handler is used by {@see MessengerTransport} for running Event Listeners (like Projectors)
 * asynchronously, after events have been committed to the event store.
 */
class EventSourcingMessageHandler implements MessageBusInterface
{



    public function dispatch($message, array $stamps = []): Envelope
    {
        
        /*
        $eventListenerInvoker = new EventListenerInvoker(
            $eventStore,
            $listener,
            $this->connection
        );
        */

        #$eventListenerInvoker->catchUp();
    }
}
