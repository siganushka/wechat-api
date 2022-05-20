<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests\Message\Template;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\Message\Template\Template;

class TemplateTest extends TestCase
{
    public function testAll(): void
    {
        $template = new Template('xyz');
        static::assertSame([], $template->getData());

        $template->addData('foo', 'value111', '#ff0000');
        $template->addData('bar', 'value222');

        static::assertTrue($template->hasData('foo'));
        static::assertTrue($template->hasData('bar'));
        static::assertSame('xyz', $template->getId());
        static::assertSame([
            'foo' => ['value' => 'value111', 'color' => '#ff0000'],
            'bar' => ['value' => 'value222'],
        ], $template->getData());

        $template->removeData('foo');
        static::assertFalse($template->hasData('foo'));
        static::assertTrue($template->hasData('bar'));
        static::assertSame([
            'bar' => ['value' => 'value222'],
        ], $template->getData());
    }
}
