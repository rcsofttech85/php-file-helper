<?php

namespace Rcsofttech85\FileHandler\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Dotenv\Dotenv;

class ServiceContainer
{
    public function getContainerBuilder(): ContainerBuilder
    {
        $dotenv = new Dotenv();
        $dotenv->load('.env');


        $containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../../src/config'));
        $loader->load('services.yaml');

        foreach ($_ENV as $key => $value) {
            $containerBuilder->setParameter($key, $value);
        }


        return $containerBuilder;
    }
}
