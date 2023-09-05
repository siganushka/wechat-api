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
    public static function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);
        OptionSet::secret($resolver);
    }
}
