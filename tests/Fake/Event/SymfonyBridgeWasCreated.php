<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Fake\Event;

use Neos\EventSourcing\Event\DomainEventInterface;

class SymfonyBridgeWasCreated implements DomainEventInterface
{
    public function __construct()
    {

    }
}