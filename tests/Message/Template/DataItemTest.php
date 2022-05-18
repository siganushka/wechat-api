<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Message\Template;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Message\Template\DataItem;

class DataItemTest extends TestCase
{
    public function testAll(): void
    {
        $item = new DataItem('foo', 'value111', '#ff0000');
        static::assertSame('foo', $item->getIndex());
        static::assertSame('value111', $item->getValue());
        static::assertSame('#ff0000', $item->getColor());
        static::assertSame(['value' => 'value111', 'color' => '#ff0000'], $item->toArray());
    }

    public function testEmptyColor(): void
    {
        $item = new DataItem('bar', 'value222');
        static::assertSame('bar', $item->getIndex());
        static::assertSame('value222', $item->getValue());
        static::assertNull($item->getColor());
        static::assertSame(['value' => 'value222'], $item->toArray());
    }
}
