<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

class HelperSet
{
    /**
     * Returns string of timestamp.
     */
    public static function getTimestamp(): string
    {
        return (string) time();
    }

    /**
     * Returns nonce string.
     */
    public static function getNonceStr(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Retuens client ip.
     */
    public static function getClientIp(): string
    {
        return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    /**
     * Returns current full url.
     */
    public static function getCurrentUrl(): string
    {
        return (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
            ($_SERVER['HTTP_HOST'] ?? 'localhost').
            ($_SERVER['REQUEST_URI'] ?? '');
    }
}
