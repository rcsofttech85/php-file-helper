<?php

namespace rcsofttech85\FileHandler;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class Container
{
    protected static ContainerBuilder $container;

    public static function setUpContainer(): void
    {
        self::$container = new ContainerBuilder();
        $loader = new YamlFileLoader(self::$container, new FileLocator(__DIR__ . '/config/'), 'dev');
        $loader->load('services.yaml');
    }

    public static function getService(string $id): null|object
    {
        return self::$container->get($id);
    }
}
