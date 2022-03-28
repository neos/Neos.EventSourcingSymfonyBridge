<?php

declare(strict_types=1);

namespace Neos\EventSourcing\SymfonyBridge\DependencyInjection;

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
