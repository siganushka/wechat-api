<?php

declare(strict_types=1);

namespace Siganushka\ApiClient\Wechat;

use Siganushka\ApiClient\OptionsResolvableTrait;
use Siganushka\ApiClient\RequestOptionsExtensionInterface;
use Siganushka\ApiClient\Wechat\Core\AccessToken;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConfigurationOptions implements RequestOptionsExtensionInterface
{
    use OptionsResolvableTrait;

    private ConfigurationManager $configurationManager;

    public function __construct(ConfigurationManager $configurationManager)
    {
        $this->configurationManager = $configurationManager;
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->define('using_config')
            ->default($this->configurationManager->getDefaultName())
            ->allowedTypes('string')
        ;

        $configurationResolver = new OptionsResolver();
        Configuration::apply($configurationResolver);

        foreach ($configurationResolver->getDefinedOptions() as $name) {
            $resolver->setDefault($name, function (Options $options) use ($name) {
                $configuration = $this->configurationManager->get($options['using_config']);

                return $configuration[$name] ?? null;
            });
        }

        unset($configurationResolver);
    }

    public static function getExtendedRequests(): iterable
    {
        return [
            AccessToken::class,
        ];
    }
}
