<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Tests;

use PHPUnit\Framework\TestCase;
use Siganushka\ApiClient\Wechat\SerializerUtils;

class SerializerUtilsTest extends TestCase
{
    public function testAll(): void
    {
        $data = [
            'foo' => 'hello world',
            'bar' => 16,
            'baz' => [
                'baz_1' => 'test',
                'baz_3' => true,
            ],
        ];

        $expectedXml = '<?xml version="1.0"?><response><foo>hello world</foo><bar>16</bar><baz><baz_1>test</baz_1><baz_3>1</baz_3></baz></response>';

        static::assertXmlStringEqualsXmlString($expectedXml, $xml = SerializerUtils::xmlEncode($data));
        static::assertEquals($data, SerializerUtils::xmlDecode($xml));
    }
}
