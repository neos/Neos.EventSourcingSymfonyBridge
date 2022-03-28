<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Infrastructure\EventStore;

use Neos\EventSourcing\Event\DomainEvents;
use Neos\EventSourcing\EventStore\StreamName;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\Event\SymfonyBridgeWasCreated;

class WritingEventsTest extends AbstractEventsTest
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     */
    public function writeEventToStore()
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

        // the the event is stored
        $eventStream = $this->eventStore->load(StreamName::fromString('symfony-bridge'));
        foreach ($eventStream as $eventEnvelope) {
            $rawEvent = $eventEnvelope->getRawEvent();
        }

        $this->assertEquals(
            'Symfony Bridge for Eventsourcing',
            $rawEvent->getPayload()['author']
        );
    }
}
