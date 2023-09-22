<?php

namespace Rcsofttech85\FileHandler\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ServiceContainer
{
    public function getContainerBuilder(): ContainerBuilder
    {
        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../../src/config'));
        $loader->load('services.yaml');
        return $containerBuilder;
    }
}
