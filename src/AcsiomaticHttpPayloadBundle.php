<?php

namespace Acsiomatic\HttpPayloadBundle;

use Acsiomatic\HttpPayloadBundle\FileUpload\Preset;
use Acsiomatic\HttpPayloadBundle\FileUpload\PresetFactory;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

final class AcsiomaticHttpPayloadBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import(__DIR__.'/../config/definition/*.php');
    }

    /**
     * @param array<mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import(__DIR__.'/../config/service/*.php');

        $services = $container->services();

        \assert(\is_array($config['request_body']));
        foreach ($config['request_body'] as $name => $arguments) {
            $builder->setDefinition(
                sprintf('acsiomatic.http_payload.request_body.preset.%s', $name),
                (new ChildDefinition('acsiomatic.http_payload.request_body.preset'))
                    ->replaceArgument('$name', $name)
                    ->replaceArgument('$formats', $arguments['formats'])
                    ->replaceArgument('$deserializationContext', $arguments['deserialization_context'])
                    ->replaceArgument('$validationGroups', $arguments['validation_groups'])
                    ->addTag('acsiomatic.http_payload.request_body.preset')
            );
        }

        \assert(\is_array($config['response_body']));
        foreach ($config['response_body'] as $name => $arguments) {
            $builder->setDefinition(
                sprintf('acsiomatic.http_payload.response_body.preset.%s', $name),
                (new ChildDefinition('acsiomatic.http_payload.response_body.preset'))
                    ->replaceArgument('$name', $name)
                    ->replaceArgument('$formats', $arguments['formats'])
                    ->replaceArgument('$serializationContext', $arguments['serialization_context'])
                    ->addTag('acsiomatic.http_payload.response_body.preset')
            );
        }

        \assert(\is_array($config['file_upload']));
        foreach ($config['file_upload'] as $name => $arguments) {
            $services->set(sprintf('acsiomatic.http_payload.file_upload.preset.%s', $name))
                ->class(Preset::class)
                ->tag('acsiomatic.http_payload.file_upload.preset')
                ->factory(service(PresetFactory::class))
                ->args([
                    $name,
                    $arguments['constraints'],
                ]);
        }
    }
}
