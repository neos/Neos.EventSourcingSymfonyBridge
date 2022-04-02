<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Infrastructure\EventStore;

use Doctrine\DBAL\Exception;
use Neos\EventSourcing\Event\DomainEvents;
use Neos\EventSourcing\EventListener\EventListenerInvoker;
use Neos\EventSourcing\EventListener\Exception\EventCouldNotBeAppliedException;
use Neos\EventSourcing\EventStore\StreamName;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\Event\SymfonyBridgeWasCreated;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\EventSubscriber\SymfonyBridgeSubscriber;
use Symfony\Component\Serializer\Exception\ExceptionInterface;

class ReactingToEventsTest extends AbstractEventsTest
{
    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->connection->executeStatement('TRUNCATE TABLE symfony_bridge;');
    }

    /**
     * @test
     * @throws EventCouldNotBeAppliedException
     * @throws ExceptionInterface
     */
    public function reactToEvents()
    {
        // given an event
        $event = new SymfonyBridgeWasCreated(
            'Symfony Bridge for Eventsourcing'
        );

        // when this event is committed
        $stream = StreamName::fromString('symfony-bridge');
        $this->eventStore->commit($stream, DomainEvents::withSingleEvent(
            $event
        ));

        $eventListenerInvoker = new EventListenerInvoker(
            $this->eventStore,
            new SymfonyBridgeSubscriber(),
            $this->connection
        );

        $appliedEventsCounter = 0;
        $eventListenerInvoker->onProgress(static function () use (&$appliedEventsCounter) {
            $appliedEventsCounter++;
        });

        // then the event listener is called
        $eventListenerInvoker->catchUp();

        $this->assertEquals(
            1,
            $appliedEventsCounter
        );
    }

    /**
     * @throws Exception
     */
    public function tearDown(): void
    {
        parent::tearDown();

        $this->connection->executeStatement('TRUNCATE TABLE neos_eventsourcing_eventlistener_appliedeventslog;');
    }
}
