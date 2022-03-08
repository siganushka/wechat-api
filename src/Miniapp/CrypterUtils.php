<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Miniapp;

class CrypterUtils
{
    /**
     * 解密微信已加密信息.
     *
     * @see https://developers.weixin.qq.com/miniprogram/dev/framework/open-ability/signature.html#%E5%8A%A0%E5%AF%86%E6%95%B0%E6%8D%AE%E8%A7%A3%E5%AF%86%E7%AE%97%E6%B3%95
     *
     * @param string $sessionKey    会话密钥
     * @param string $encryptedData 包括敏感数据在内的完整用户信息的加密数据
     * @param string $iv            加密算法的初始向量
     *
     * @throws \InvalidArgumentException 输入参数无效
     * @throws \RuntimeException         数据解密失败
     *
     * @return array<string, mixed> 已解密的用户数据
     */
    public static function decrypt(string $sessionKey, string $encryptedData, string $iv): array
    {
        if (24 !== mb_strlen($sessionKey)) {
            throw new \InvalidArgumentException('The argument "session_key" is invalid.');
        }

        if (24 !== mb_strlen($iv)) {
            throw new \InvalidArgumentException('The argument "iv" is invalid.');
        }

        $aesKey = base64_decode($sessionKey);
        $aesCipher = base64_decode($encryptedData);
        $aesIV = base64_decode($iv);

        try {
            $result = openssl_decrypt($aesCipher, 'AES-128-CBC', $aesKey, 1, $aesIV);
        } catch (\Throwable $th) {
            throw new \RuntimeException('Unable to decrypt value.');
        }

        if (false === $result) {
            throw new \RuntimeException('Unable to decrypt value.');
        }

        /** @var array<string, mixed> */
        $data = json_decode($result, true);
        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException('Unable to decrypt value.');
        }

        return $data;
    }
}
