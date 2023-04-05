<?php

use Acsiomatic\HttpPayloadBundle\ResponseBody\Contracts\ResponseStackInterface;
use Acsiomatic\HttpPayloadBundle\ResponseBody\EventListener\ControllerListener;
use Acsiomatic\HttpPayloadBundle\ResponseBody\EventListener\ExceptionListener;
use Acsiomatic\HttpPayloadBundle\ResponseBody\EventListener\ViewListener;
use Acsiomatic\HttpPayloadBundle\ResponseBody\OutlineResolver;
use Acsiomatic\HttpPayloadBundle\ResponseBody\OutlineStack;
use Acsiomatic\HttpPayloadBundle\ResponseBody\Preset;
use Acsiomatic\HttpPayloadBundle\ResponseBody\ResponseStack;
use Acsiomatic\HttpPayloadBundle\ResponseBody\Serializer\SuccessorOfValidationFailedExceptionNormalizer;
use Acsiomatic\HttpPayloadBundle\ResponseBody\Serializer\ThrowableNormalizer;
use Symfony\Component\DependencyInjection\Argument\AbstractArgument;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services
        ->set(ControllerListener::class)
        ->set(ExceptionListener::class)
        ->set(OutlineResolver::class)
        ->set(OutlineStack::class)
        ->set(ResponseStackInterface::class, ResponseStack::class)
        ->set(ViewListener::class)
        ->set(ThrowableNormalizer::class)
            ->tag('serializer.normalizer', ['priority' => -600])
        ->set(SuccessorOfValidationFailedExceptionNormalizer::class)
            ->tag('serializer.normalizer', ['priority' => -500])
        ->set('acsiomatic.http_payload.response_body.preset', Preset::class)
            ->abstract()
            ->args([
                '$name' => new AbstractArgument(),
                '$formats' => new AbstractArgument(),
                '$serializationContext' => new AbstractArgument(),
            ]);
};
