<?php

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    /** @var ArrayNodeDefinition $root */
    $root = $definition->rootNode();
    $root->children()
        ->arrayNode('response_body')
            ->addDefaultChildrenIfNoneSet('default')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->arrayNode('formats')
                        ->defaultValue(['json'])
                        ->cannotBeEmpty()
                        ->scalarPrototype()->end()
                    ->end()
                    ->arrayNode('serialization_context')
                        ->normalizeKeys(false)
                        ->defaultValue([])
                        ->variablePrototype()->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ->end();
};
