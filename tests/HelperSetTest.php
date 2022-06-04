<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\HelperSet;

class HelperSetTest extends TestCase
{
    public function testAll(): void
    {
        static::assertIsString(HelperSet::getTimestamp());
        static::assertIsString(HelperSet::getNonceStr());
        static::assertSame('0.0.0.0', HelperSet::getClientIp());
        static::assertSame('0.0.0.0', HelperSet::getClientIp());
        static::assertSame('http://localhost', HelperSet::getCurrentUrl());
    }
}
