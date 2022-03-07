<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat\Miniapp\Request;

use Siganushka\ApiClient\AbstractRequest;
use Siganushka\ApiClient\CacheableResponseInterface;
use Siganushka\ApiClient\Exception\ParseResponseException;
use Siganushka\ApiClient\Wechat\Configuration;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @see https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/login/auth.code2Session.html
 */
class SessionKeyRequest extends AbstractRequest implements CacheableResponseInterface
{
    public const URL = 'https://api.weixin.qq.com/sns/jscode2session';

    private Configuration $configuration;

    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    protected function configureRequest(array $options): void
    {
        $query = [
            'appid' => $this->configuration['appid'],
            'secret' => $this->configuration['appsecret'],
            'grant_type' => 'authorization_code',
            'js_code' => $options['js_code'],
        ];

        $this
            ->setMethod('GET')
            ->setUrl(static::URL)
            ->setQuery($query)
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setRequired('js_code');
        $resolver->setAllowedTypes('js_code', 'string');
    }

    /**
     * @return array{ openid: string, session_key: string }
     */
    public function parseResponse(ResponseInterface $response): array
    {
        /**
         * @var array{
         *  openid: string,
         *  session_key: string,
         *  errcode?: int,
         *  errmsg?: string
         * }
         */
        $result = $response->toArray();

        $errcode = (int) ($result['errcode'] ?? 0);
        $errmsg = (string) ($result['errmsg'] ?? '');

        if (0 === $errcode) {
            return $result;
        }

        throw new ParseResponseException($response, $errmsg, $errcode);
    }

    public function getCacheTtl(): int
    {
        return 300;
    }
}
