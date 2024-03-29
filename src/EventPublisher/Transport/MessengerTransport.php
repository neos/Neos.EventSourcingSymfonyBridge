<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport;

use Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport\Messenger\Dto\EventSourcingMessage;
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
