<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Jsapi;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;

class ConfigUtilsTest extends TestCase
{
    public function testGenerate(): void
    {
        $configuration = ConfigurationTest::createConfiguration();
        $configUtils = new ConfigUtils($configuration);

        $configs = $configUtils->generate('foo');
        static::assertArrayHasKey('nonceStr', $configs);
        static::assertArrayHasKey('timestamp', $configs);
        static::assertArrayHasKey('signature', $configs);
        static::assertArrayNotHasKey('debug', $configs);
        static::assertSame('test_appid', $configs['appId']);
        static::assertSame([], $configs['jsApiList']);

        $configs = $configUtils->generate('foo', ['test_api'], true);
        static::assertSame(['test_api'], $configs['jsApiList']);
        static::assertTrue($configs['debug']);
    }
}
