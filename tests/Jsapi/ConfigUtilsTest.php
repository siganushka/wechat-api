<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Jsapi;

use Siganushka\ApiClient\Wechat\Jsapi\ConfigUtils;
use Siganushka\ApiClient\Wechat\Tests\BaseTest;
use Siganushka\ApiClient\Wechat\Tests\ConfigurationTest;

class ConfigUtilsTest extends BaseTest
{
    public function testGenerate(): void
    {
        $configuration = ConfigurationTest::createConfiguration();
        $ticket = $this->createMockTicket();

        $configUtils = new ConfigUtils($configuration, $ticket);

        $configs = $configUtils->generate();
        static::assertArrayHasKey('nonceStr', $configs);
        static::assertArrayHasKey('timestamp', $configs);
        static::assertArrayHasKey('signature', $configs);
        static::assertArrayHasKey('debug', $configs);
        static::assertSame('test_appid', $configs['appId']);
        static::assertSame([], $configs['jsApiList']);
        static::assertFalse($configs['debug']);

        $configs = $configUtils->generate(['test_api'], true);
        static::assertSame(['test_api'], $configs['jsApiList']);
        static::assertTrue($configs['debug']);
    }
}
