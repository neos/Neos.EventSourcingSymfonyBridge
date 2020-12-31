<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Unit\EventPublisher;

use Neos\EventSourcing\Event\DomainEvents;
use Neos\EventSourcing\SymfonyBridge\EventPublisher\SymfonyEventPublisher;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\Event\SymfonyBridgeWasCreated;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\InMemoryAsyncTransport;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\InMemoryEventDispatcher;
use PHPUnit\Framework\TestCase;

class SymfonyEventPublisherTest extends TestCase
{
    /**
     * @test
     */
    public function publishEventReturnsTheExpectedResult()
    {
        $inMemoryAsyncTransport =  new InMemoryAsyncTransport();

        $eventPublisher = new SymfonyEventPublisher(
            $inMemoryAsyncTransport,
            new InMemoryEventDispatcher(),
            'ABC-DEF'
        );

        $domainEvents = DomainEvents::fromArray(
            [
                new SymfonyBridgeWasCreated()
            ]
        );

        $eventPublisher->publish($domainEvents);

        var_dump($inMemoryAsyncTransport->firstDispatchedEvent());

        $this->assertEquals(1,1);
    }

}