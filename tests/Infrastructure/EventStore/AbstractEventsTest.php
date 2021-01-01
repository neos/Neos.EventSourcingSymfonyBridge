<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Infrastructure\EventStore;

use Neos\EventSourcing\Event\Resolver\FullyQualifiedClassNameResolver;
use Neos\EventSourcing\EventStore\EventNormalizer;
use Neos\EventSourcing\EventStore\EventStore;
use Neos\EventSourcing\EventStore\Storage\Doctrine\DoctrineEventStorage;
use Neos\EventSourcing\SymfonyBridge\EventListener\AppliedEventsStorage\DoctrineAppliedEventsStorageSetup;
use Neos\EventSourcing\SymfonyBridge\EventPublisher\SymfonyEventPublisher;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\InMemoryAsyncTransport;
use PHPUnit\Framework\TestCase;
use Symfony\Bridge\Doctrine\Test\DoctrineTestHelper;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractEventsTest extends TestCase
{
    protected $eventStore;
    protected $connection;

    public function setUp()
    {
        parent::setUp();

        $eventTypeResolver = new FullyQualifiedClassNameResolver();
        $eventNormalizer = new EventNormalizer($eventTypeResolver);

        $entityManager = DoctrineTestHelper::createTestEntityManager();
        $this->connection = $entityManager->getConnection();

        $eventStorage = new DoctrineEventStorage(
            ['eventTableName' => 'smyfony_bridge'],
            $eventNormalizer,
            $this->connection
        );

        $eventPublisher = new SymfonyEventPublisher(
            new InMemoryAsyncTransport(),
            new EventDispatcher(),
            'event-store-container-id'
        );

        $this->eventStore = new EventStore(
            $eventStorage,
            $eventPublisher,
            $eventNormalizer
        );

        $this->connection->beginTransaction();

        $this->eventStore->setup();

        $doctrineAppliedEventsStorageSetup = new DoctrineAppliedEventsStorageSetup($this->connection);
        $doctrineAppliedEventsStorageSetup->setup();
    }
}
