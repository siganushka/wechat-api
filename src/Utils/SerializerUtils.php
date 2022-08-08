<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Utils;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class SerializerUtils
{
    public static function xmlEncode(array $data, array $context = []): string
    {
        return (new XmlEncoder())->encode($data, XmlEncoder::FORMAT, $context);
    }

    public static function xmlDecode(string $data, array $context = [])
    {
        return (new XmlEncoder())->decode($data, XmlEncoder::FORMAT, $context);
    }
}
