<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Message\Template;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Message\Template\Data;
use Siganushka\ApiClient\Wechat\Message\Template\DataItem;

class DataTest extends TestCase
{
    public function testAll(): void
    {
        $data = new Data();
        $data->addItem(new DataItem('foo', 'value111', '#ff0000'));
        $data->addItem(new DataItem('bar', 'value222'));

        static::assertTrue($data->hasItem('foo'));
        static::assertTrue($data->hasItem('bar'));
        static::assertSame([
            'foo' => ['value' => 'value111', 'color' => '#ff0000'],
            'bar' => ['value' => 'value222'],
        ], $data->toArray());

        $data->removeItem('foo');
        static::assertFalse($data->hasItem('foo'));
        static::assertTrue($data->hasItem('bar'));
        static::assertSame([
            'bar' => ['value' => 'value222'],
        ], $data->toArray());
    }

    public function testEmptyItems(): void
    {
        $data = new Data();
        static::assertFalse($data->hasItem('foo'));
        static::assertSame([], $data->toArray());
    }
}
