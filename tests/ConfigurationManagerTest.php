<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Configuration;
use Siganushka\ApiClient\Wechat\ConfigurationManager;

class ConfigurationManagerTest extends TestCase
{
    public function testAll(): void
    {
        $configurationManager = static::create();

        static::assertSame('default', $configurationManager->getDefaultName());
        static::assertEquals(['default', 'custom'], array_keys($configurationManager->all()));
        static::assertTrue($configurationManager->has('default'));
        static::assertTrue($configurationManager->has('custom'));
        static::assertFalse($configurationManager->has('foo'));
        static::assertInstanceOf(Configuration::class, $configurationManager->get('default'));
        static::assertInstanceOf(Configuration::class, $configurationManager->get('custom'));
    }

    public function testGetDoesNotExistException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Configuration "foo" for "%s" does not exist. Defined are: "default", "custom"', ConfigurationManager::class));

        $configurationManager = static::create();
        $configurationManager->get('foo');
    }

    public function testSetAlreadyExistsException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(sprintf('Configuration "custom" for "%s" already exists.', ConfigurationManager::class));

        $configurationManager = static::create();
        $configurationManager->set('custom', ConfigurationTest::create());
    }

    public static function create(string $defaultName = 'default'): ConfigurationManager
    {
        $configurationManager = new ConfigurationManager($defaultName);
        $configurationManager->set($defaultName, ConfigurationTest::create());
        $configurationManager->set('custom', ConfigurationTest::create([
            'appid' => 'custom_appid',
            'secret' => 'custom_secret',
        ]));

        return $configurationManager;
    }
}
