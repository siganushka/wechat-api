<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat\Tests\Template;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiFactory\Wechat\Template\Template;

class TemplateTest extends TestCase
{
    public function testAll(): void
    {
        $template = new Template('xyz');
        static::assertEquals([], $template->getData());

        $template->addData('foo', 'value111', '#ff0000');
        $template->addData('bar', 'value222');

        static::assertTrue($template->hasData('foo'));
        static::assertTrue($template->hasData('bar'));
        static::assertSame('xyz', $template->getId());
        static::assertEquals([
            'foo' => ['value' => 'value111', 'color' => '#ff0000'],
            'bar' => ['value' => 'value222'],
        ], $template->getData());

        $template->removeData('foo');
        static::assertFalse($template->hasData('foo'));
        static::assertTrue($template->hasData('bar'));
        static::assertEquals([
            'bar' => ['value' => 'value222'],
        ], $template->getData());
    }
}
