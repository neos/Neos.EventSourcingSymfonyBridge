<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Messenger\Handler;

use Doctrine\DBAL\Connection;
use Neos\EventSourcing\EventListener\EventListenerInvoker;
use Neos\EventSourcing\EventListener\Exception\EventCouldNotBeAppliedException;
use Neos\EventSourcing\SymfonyBridge\Resources\EventSourcingMessage;
use Neos\EventSourcing\SymfonyBridge\Transport\MessengerTransport;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * This message handler is used by {@see MessengerTransport} for running Event Listeners (like Projectors)
 * asynchronously, after events have been committed to the event store.
 */
class EventSourcingMessageHandler implements MessageHandlerInterface
{
    /**
     * @var ContainerInterface
     */
    protected ContainerInterface $container;

    /**
     * @var Connection
     */
    protected Connection $connection;

    public function __construct(
        ContainerInterface $container,
        Connection $connection
    ) {
        $this->container = $container;
        $this->connection = $connection;
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws EventCouldNotBeAppliedException
     */
    public function __invoke(EventSourcingMessage $message): void
    {
        $listener = $this->container->get($message->listenerClassName);
        $eventStore = $this->container->get($message->eventStoreContainerId);

        $eventListenerInvoker = new EventListenerInvoker(
            $eventStore,
            $listener,
            $this->connection
        );

        $eventListenerInvoker->catchUp();
    }
}
