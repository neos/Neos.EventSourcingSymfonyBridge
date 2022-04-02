<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport\Console;

use Doctrine\DBAL\Connection;
use Neos\EventSourcing\EventListener\EventListenerInvoker;
use Neos\EventSourcing\EventListener\Exception\EventCouldNotBeAppliedException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This is an implementation detail of {@see ConsoleCommandTransport} for running Event Listeners (like Projectors)
 * asynchronously by spawning a new PHP process.
 */
final class InternalCatchUpEventListenerCommand extends Command
{
    protected static $defaultName = 'eventsourcing:internal:catchup-event-listener';

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
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->addArgument('eventListenerClassName', InputArgument::REQUIRED)
            ->addArgument('eventStoreContainerId', InputArgument::REQUIRED);
    }

    /**
     * @throws NotFoundExceptionInterface
     * @throws ContainerExceptionInterface
     * @throws EventCouldNotBeAppliedException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $eventListenerClassName = $input->getArgument('eventListenerClassName');
        $eventStoreContainerId = $input->getArgument('eventStoreContainerId');

        $listener = $this->container->get($eventListenerClassName);
        $eventStore = $this->container->get($eventStoreContainerId);

        $eventListenerInvoker = new EventListenerInvoker(
            $eventStore,
            $listener,
            $this->connection
        );

        $eventListenerInvoker->catchUp();

        return Command::SUCCESS;
    }
}
