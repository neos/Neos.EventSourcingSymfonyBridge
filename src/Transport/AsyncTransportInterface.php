<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Transport;

interface AsyncTransportInterface {

    public function send(
        string $listenerClassName,
        string $eventStoreContainerId
    ): void;
}