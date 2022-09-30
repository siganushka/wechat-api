<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat;

use Siganushka\ApiFactory\AbstractConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Wechat configuration.
 */
class Configuration extends AbstractConfiguration
{
    protected function configureOptions(OptionsResolver $resolver): void
    {
        OptionsUtils::appid($resolver);
        OptionsUtils::secret($resolver);
    }
}
