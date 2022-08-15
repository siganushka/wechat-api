<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Siganushka\ApiClient\RequestOptionsExtensionInterface;
use Siganushka\ApiClient\RequestOptionsExtensionTrait;
use Siganushka\ApiClient\Wechat\Core\Token;
use Siganushka\ApiClient\Wechat\Miniapp\SessionKey;
use Siganushka\ApiClient\Wechat\OAuth\RefreshToken;
use Siganushka\ApiClient\Wechat\Payment\Query;
use Siganushka\ApiClient\Wechat\Payment\Refund;
use Siganushka\ApiClient\Wechat\Payment\Transfer;
use Siganushka\ApiClient\Wechat\Payment\Unifiedorder;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationOptions implements RequestOptionsExtensionInterface
{
    use RequestOptionsExtensionTrait;

    private ConfigurationManager $configurationManager;

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $defaultName = $this->configurationManager->getDefaultName();
        $configurations = $this->configurationManager->all();

        WechatOptions::using_config($resolver)
            ->default($defaultName)
            ->allowedValues(...array_keys($configurations))
        ;

        $newResolver = new OptionsResolver();
        Configuration::apply($newResolver);

        // $definedOptions = array_intersect($resolver->getDefinedOptions(), $newResolver->getDefinedOptions());
        // unset($newResolver);

        foreach ($newResolver->getDefinedOptions() as $option) {
            $resolver->setDefault($option, function (Options $options) use ($option) {
                return $this->configurationManager->get($options['using_config'])[$option] ?? null;
            });
        }
    }

    public static function getExtendedRequests(): array
    {
        return [
            Token::class,
            SessionKey::class,
            RefreshToken::class,
            Query::class,
            Refund::class,
            Transfer::class,
            Unifiedorder::class,
        ];
    }
}
