<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication;

use Acsiomatic\HttpPayloadBundle\AcsiomaticHttpPayloadBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

final class DummyKernel extends Kernel
{
    use MicroKernelTrait;

    public function __construct(
        private readonly string|null $configFilename,
    ) {
        parent::__construct('test', false);
    }

    public function registerBundles(): iterable
    {
        return [
            new FrameworkBundle(),
            new AcsiomaticHttpPayloadBundle(),
        ];
    }

    public function getCacheDir(): string
    {
        return __DIR__.'/../../../var/phpunit/dummy-application/cache-'.hash('md5', (string) $this->configFilename);
    }

    public function getLogDir(): string
    {
        return __DIR__.'/../../../var/phpunit/dummy-application/log';
    }

    // @phpstan-ignore-next-line Method is actually used by MicroKernelTrait
    private function configureContainer(ContainerConfigurator $container): void
    {
        $container->extension('framework', [
            'http_method_override' => false,
        ]);

        // Allows service injecting in route controller
        $container->services()
            ->defaults()
            ->autowire()
            ->autoconfigure()
            ->load(__NAMESPACE__.'\\Controller\\', __DIR__.'/Controller');

        if ($this->configFilename !== null) {
            $container->import(__DIR__.'/config/'.$this->configFilename);
        }
    }

    // @phpstan-ignore-next-line Method is actually used by MicroKernelTrait
    private function configureRoutes(RoutingConfigurator $routes): void
    {
        $routes->import(__DIR__.'/Controller', 'attribute');
    }
}
