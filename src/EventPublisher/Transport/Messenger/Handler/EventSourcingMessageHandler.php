<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport\Messenger\Handler;

use Doctrine\DBAL\Connection;
use Neos\EventSourcing\EventListener\EventListenerInvoker;
use Neos\EventSourcing\EventListener\Exception\EventCouldNotBeAppliedException;
use Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport\Messenger\Dto\EventSourcingMessage;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;

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
        $eventStore = $this->container->get($message->eventStoreContainerId);
        $listener = $this->container->get($message->listenerClassName);

        $eventListenerInvoker = new EventListenerInvoker(
            $eventStore,
            $listener,
            $this->connection
        );

        $eventListenerInvoker->catchUp();
    }
}
