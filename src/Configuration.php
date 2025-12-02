<?php

declare(strict_types=1);

namespace Siganushka\ApiFactory\Wechat;

use Siganushka\ApiFactory\AbstractConfiguration;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractConfiguration<array{ appid: string, secret: string }>
 */
class Configuration extends AbstractConfiguration
{
    public static function configureOptions(OptionsResolver $resolver): void
    {
        OptionSet::appid($resolver);
        OptionSet::secret($resolver);
    }
}
