<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\DependencyInjection;

use Doctrine\DBAL\Connection;
use Neos\EventSourcing\EventStore\EventStore;
use Neos\EventSourcing\SymfonyBridge\EventPublisher\SymfonyEventPublisher;
use Psr\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Main configuration/integration point for Symfony
 */
class NeosEventSourcingExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        // Load configuration
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('neos_eventsourcing', $config);

        // load services.yaml
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../../config')
        );
        $loader->load('services.yaml');

        foreach ($config['stores'] as $name => $store) {
            $container->register('neos_eventsourcing.eventstore.' . $name)
                ->setClass(EventStore::class)
                ->setPublic(true)
                ->setArgument('$storage', new Reference('neos_eventsourcing.eventstore.' . $name . '.storage'))
                ->setArgument('$eventPublisher', new Reference('neos_eventsourcing.eventstore.' . $name . '.publisher'))
                ->setArgument('$eventNormalizer', new Reference('neos_eventsourcing_eventStore_eventNormalizer'));

            $container->register('neos_eventsourcing.eventstore.' . $name . '.storage')
                ->setClass($store['storage'])
                ->setArgument('$options', ['eventTableName' => $store['eventTableName']])
                ->setArgument('$eventNormalizer', new Reference('neos_eventsourcing_eventStore_eventNormalizer'))
                ->setArgument('$connection', new Reference(Connection::class));

            $container->register('neos_eventsourcing.eventstore.' . $name . '.publisher')
                ->setClass(SymfonyEventPublisher::class)
                ->setArgument('$asyncTransport', new Reference($store['eventPublisherTransport']))
                ->setArgument('$eventDispatcher', new Reference(EventDispatcherInterface::class))
                ->setArgument('$eventStoreContainerId', 'neos_eventsourcing.eventstore.' . $name);
        }
    }

    public function getAlias()
    {
        return 'neos_eventsourcing';
    }
}
