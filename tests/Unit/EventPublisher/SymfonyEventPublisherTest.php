<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Unit\EventPublisher;

use Neos\EventSourcing\Event\DomainEvents;
use Neos\EventSourcing\SymfonyBridge\EventPublisher\SymfonyEventPublisher;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\Event\SymfonyBridgeWasCreated;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\EventSubscriber\SymfonyBridgeSubscriber;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\InMemoryAsyncTransport;
use Symfony\Component\EventDispatcher\EventDispatcher;
use PHPUnit\Framework\TestCase;

class SymfonyEventPublisherTest extends TestCase
{
    private $inMemoryAsyncTransport;
    private $eventPublisher;
    private $domainEvents;

    public function setUp()
    {
        $this->inMemoryAsyncTransport = new InMemoryAsyncTransport();

        $inMemoryEventDispatcher = new EventDispatcher();
        $inMemoryEventDispatcher->addListener(
            SymfonyBridgeWasCreated::class,
            [new SymfonyBridgeSubscriber(), 'whenSymfonyBridgeWasCreated']
        );

        $this->eventPublisher = new SymfonyEventPublisher(
            $this->inMemoryAsyncTransport,
            $inMemoryEventDispatcher,
            'neos_store'
        );

        $this->domainEvents = DomainEvents::fromArray(
            [
                new SymfonyBridgeWasCreated()
            ]
        );
    }

    /**
     * @test
     */
    public function publishEventReturnsTheExpectedResult()
    {
        // given an event to publish

        // when the evnt is published
        $this->eventPublisher->publish(
            $this->domainEvents
        );

        // then the expected event is dispatched
        $firstDispatchedEvent = $this->inMemoryAsyncTransport->firstDispatchedEvent();

        $this->assertEquals(
            'neos_store',
            $firstDispatchedEvent->eventStoreContainerId()
        );

        $this->assertEquals(
            SymfonyBridgeSubscriber::class,
            $firstDispatchedEvent->listenerClassName()
        );
    }
}
