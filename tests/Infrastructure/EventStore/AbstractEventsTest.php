<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Tests\Infrastructure\EventStore;

use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Neos\EventSourcing\EventStore\EventNormalizer;
use Neos\EventSourcing\EventStore\EventStore;
use Neos\EventSourcing\EventStore\Storage\Doctrine\DoctrineEventStorage;
use Neos\EventSourcing\SymfonyBridge\Event\Resolver\FullyQualifiedClassNameResolver;
use Neos\EventSourcing\SymfonyBridge\EventListener\AppliedEventsStorage\DoctrineAppliedEventsStorageSetup;
use Neos\EventSourcing\SymfonyBridge\EventPublisher\SymfonyEventPublisher;
use Neos\EventSourcing\SymfonyBridge\Tests\Fake\InMemoryAsyncTransport;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

abstract class AbstractEventsTest extends KernelTestCase
{
    protected EventStore $eventStore;
    protected Connection $connection;

    public function setUp(): void
    {
        parent::setUp();

        $eventTypeResolver = new FullyQualifiedClassNameResolver();
        $eventNormalizer = new EventNormalizer($eventTypeResolver);

        self::bootKernel();
        $container = static::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);
        $this->connection = $entityManager->getConnection();

        $eventStorage = new DoctrineEventStorage(
            ['eventTableName' => 'symfony_bridge'],
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

        $this->eventStore->setup();

        $doctrineAppliedEventsStorageSetup = new DoctrineAppliedEventsStorageSetup($this->connection);
        $doctrineAppliedEventsStorageSetup->setup();
    }
}
