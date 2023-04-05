<?php

use Acsiomatic\HttpPayloadBundle\RequestBody\OutlineResolver;
use Acsiomatic\HttpPayloadBundle\RequestBody\Preset;
use Acsiomatic\HttpPayloadBundle\RequestBody\ValueResolver\RequestBodyValueResolver;
use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services
        ->set(OutlineResolver::class)
        ->set(RequestBodyValueResolver::class)
        ->set('acsiomatic.http_payload.request_body.preset', Preset::class)
            ->abstract()
            ->args([
                '$name' => new AbstractArgument(),
                '$formats' => new AbstractArgument(),
                '$deserializationContext' => new AbstractArgument(),
                '$validationGroups' => new AbstractArgument(),
            ]);
};
