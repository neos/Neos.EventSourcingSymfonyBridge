<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Fake\EventSubscriber;

use Neos\EventSourcing\EventStore\RawEvent;
use Neos\EventSourcing\Projection\ProjectorInterface;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\Event\SymfonyBridgeWasCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SymfonyBridgeSubscriber implements ProjectorInterface, EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SymfonyBridgeWasCreated::class =>  ['whenSymfonyBridgeWasCreated']
        ];
    }

    public function whenSymfonyBridgeWasCreated(SymfonyBridgeWasCreated $event, RawEvent $rawEvent)
    {
    }

    public function reset(): void
    {
        // TODO: Implement reset() method.
    }
}
