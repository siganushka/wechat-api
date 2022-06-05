<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Symfony\Component\Serializer\Encoder\XmlEncoder;

class SerializerUtils
{
    /**
     * @param array<string, mixed> $data
     * @param array{
     *  as_collection?: string,
     *  load_options?: boolean,
     *  remove_empty_tags?: boolean,
     *  xml_type_cast_attributes?: boolean,
     *  xml_root_node_name?: string,
     *  xml_standalone?: string,
     *  xml_version?: string,
     *  xml_format_output?: boolean,
     *  xml_encoding?: string
     * } $context
     */
    public static function xmlEncode(array $data, array $context = []): string
    {
        return (new XmlEncoder())->encode($data, XmlEncoder::FORMAT, $context);
    }

    /**
     * @param array{
     *  as_collection?: string,
     *  load_options?: boolean,
     *  remove_empty_tags?: boolean,
     *  xml_type_cast_attributes?: boolean,
     *  xml_root_node_name?: string,
     *  xml_standalone?: string,
     *  xml_version?: string,
     *  xml_format_output?: boolean,
     *  xml_encoding?: string
     * } $context
     *
     * @return mixed
     */
    public static function xmlDecode(string $data, array $context = [])
    {
        return (new XmlEncoder())->decode($data, XmlEncoder::FORMAT, $context);
    }
}
