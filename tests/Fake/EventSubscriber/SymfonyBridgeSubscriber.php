<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Fake\EventSubscriber;

use App\Domain\Context\Blogging\Event\BlogWasCreated;
use Neos\EventSourcing\EventStore\RawEvent;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\Event\SymfonyBridgeWasCreated;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SymfonyBridgeSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            SymfonyBridgeWasCreated::class =>  ['whenSymfonyBridgeWasCreated']
        ];
    }

    public function whenSymfonyBridgeWasCreated(BlogWasCreated $event, RawEvent $rawEvent)
    {

    }
}
