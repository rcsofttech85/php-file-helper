<?php

namespace Base;

use PHPUnit\Framework\TestCase;
use rcsofttech85\FileHandler\DI\ServiceContainer;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class BaseTest extends TestCase
{
    /**
     * @var array<string>|null
     */
    protected static array|null $files = [];

    private static ContainerBuilder|null $containerBuilder;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        $serviceContainer = new ServiceContainer();
        self::$containerBuilder = $serviceContainer->getContainerBuilder();
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

    protected function isFileValid(string $filename): mixed
    {
        if (!file_exists($filename) || !$data = file_get_contents($filename)) {
            $this->fail('file does not exists or has no content');
        }
        return $data;
    }

    protected function setObjectHandler(string $classname, string $serviceId): mixed
    {
        $objectHandler = self::$containerBuilder->get($serviceId);

        if (!is_a($objectHandler, $classname)) {
            $this->fail("provided service is not an instance of " . $classname);
        }
        return $objectHandler;
    }
}
