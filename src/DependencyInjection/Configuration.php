<?php

namespace Neos\EventSourcing\SymfonyBridge\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function __construct()
    {
    }

    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('neos_eventsourcing');
        $rootNode    = $treeBuilder->getRootNode();

        $this->addDbalSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Add DBAL section to configuration tree
     */
    private function addDbalSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('stores')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('eventTableName')->end()
                            ->arrayNode('listenerClassNames')
                                ->scalarPrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
    }
}
