<?php

use Acsiomatic\HttpPayloadBundle\FileUpload\OutlineResolver;
use Acsiomatic\HttpPayloadBundle\FileUpload\PresetFactory;
use Acsiomatic\HttpPayloadBundle\FileUpload\ValueResolver\UploadedFileValueResolver;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $container) {
    $services = $container->services()
        ->defaults()
            ->autowire()
            ->autoconfigure();

    $services
        ->set(UploadedFileValueResolver::class)
        ->set(OutlineResolver::class)
        ->set(PresetFactory::class);
};
