<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Transport;

use Neos\EventSourcing\SymfonyBridge\Resources\EventSourcingMessage;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerTransport implements AsyncTransportInterface
{
    private MessageBusInterface $messageBus;

    public function __construct(
        MessageBusInterface $messageBus
    )
    {
        $this->messageBus = $messageBus;
    }

    public function send(
        string $listenerClassName,
        string $eventStoreContainerId
    ): void {
        $message = EventSourcingMessage::create(
            $listenerClassName,
            $eventStoreContainerId
        );

        $this->messageBus->dispatch(
            $message
        );
    }
}
