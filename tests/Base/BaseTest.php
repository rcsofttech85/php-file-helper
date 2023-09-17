<?php

namespace Base;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class BaseTest extends TestCase
{
    protected static array|null $files = [];

    protected static ContainerBuilder|null $containerBuilder;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$containerBuilder = new ContainerBuilder();
        $loader = new YamlFileLoader(self::$containerBuilder, new FileLocator(__DIR__ . '/../../src/config'), 'dev');
        $loader->load('services.yaml');

        $content = "Film,Genre,Lead Studio,Audience score %,Profitability,Rotten Tomatoes %,Worldwide Gross,Year\n"
            . "Zack and Miri Make a Porno,Romance,The Weinstein Company,70,1.747541667,64,$41.94 ,2008\n"
            . "Youth in Revolt,Comedy,The Weinstein Company,52,1.09,68,$19.62 ,2010\n"
            . "Twilight,Romance,Independent,68,6.383363636,26,$702.17 ,2011";

        file_put_contents('movie.csv', $content);
    }

    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();
        foreach (static::$files as $file) {
            if (file_exists($file)) {
                unlink(filename: $file);
            }
        }
        static::$files = null;
    }
}
