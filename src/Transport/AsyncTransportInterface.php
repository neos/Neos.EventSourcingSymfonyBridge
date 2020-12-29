<?php

namespace Neos\EventSourcing\SymfonyBridge\Transport;

interface AsyncTransportInterface {

    public function send(
        string $listenerClassName,
        string $eventStoreContainerId
    );
}