<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport\Messenger\Dto;

final class EventSourcingMessage
{
    public string $listenerClassName;

    public string $eventStoreContainerId;

    public static function create(
        string $listenerClassName,
        string $eventStoreContainerId
    ): EventSourcingMessage
    {
        $newMessage = new self();
        $newMessage->listenerClassName = $listenerClassName;
        $newMessage->eventStoreContainerId = $eventStoreContainerId;

        return $newMessage;
    }
}
