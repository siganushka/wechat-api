<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\OptionsExtensionInterface;
use Siganushka\ApiClient\RequestInterface;
use Siganushka\ApiClient\Wechat\WechatExtension;

class WechatExtensionTest extends TestCase
{
    public function testAll(): void
    {
        $configuration = ConfigurationTest::create();

        $extension = new WechatExtension($configuration);
        static::assertCount(17, $extension->loadRequests());
        static::assertCount(3, $extension->loadOptionsExtensions());

        foreach ($extension->loadRequests() as $request) {
            static::assertInstanceOf(RequestInterface::class, $request);
        }

        foreach ($extension->loadOptionsExtensions() as $extension) {
            static::assertInstanceOf(OptionsExtensionInterface::class, $extension);
        }
    }
}
