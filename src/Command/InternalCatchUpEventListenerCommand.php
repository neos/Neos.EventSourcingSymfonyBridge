<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\Command;

use Doctrine\DBAL\Connection;
use Neos\EventSourcing\EventListener\EventListenerInvoker;
use Neos\EventSourcing\SymfonyBridge\Transport\ConsoleCommandTransport;
use Psr\Container\ContainerInterface;
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
    protected $container;

    /**
     * @var Connection
     */
    protected $connection;

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

    protected function execute(InputInterface $input, OutputInterface $output)
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
