<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\DependencyInjection;

use Neos\EventSourcing\EventStore\Storage\Doctrine\DoctrineEventStorage;
use Neos\EventSourcing\SymfonyBridge\EventPublisher\Transport\ConsoleCommandTransport;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('neos_eventsourcing');
        $rootNode    = $treeBuilder->getRootNode();

        $this->addStoresSection($rootNode);
        return $treeBuilder;
    }

    /**
     * Add stores section to configuration tree
     */
    private function addStoresSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('stores')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('eventTableName')->isRequired()->cannotBeEmpty()->end()

                            ->scalarNode('storage')->defaultValue(DoctrineEventStorage::class)->end()
                            ->scalarNode('eventPublisherTransport')->defaultValue(ConsoleCommandTransport::class)->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
