<?php

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition) {
    /** @var ArrayNodeDefinition $root */
    $root = $definition->rootNode();
    $root->children()
        ->arrayNode('file_upload')
            ->addDefaultChildrenIfNoneSet('default')
            ->useAttributeAsKey('name')
            ->arrayPrototype()
                ->children()
                    ->arrayNode('constraints')
                        ->normalizeKeys(false)
                        ->defaultValue([])
                        ->variablePrototype()->end()
                    ->end()
                ->end()
            ->end()
        ->end()
    ->end();
};
