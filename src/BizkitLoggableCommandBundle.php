<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle;

use Bizkit\LoggableCommandBundle\DependencyInjection\Compiler\ExcludeMonologChannelPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class BizkitLoggableCommandBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        // Needs to happen before LoggerChannelPass.
        $container->addCompilerPass(new ExcludeMonologChannelPass(), PassConfig::TYPE_BEFORE_OPTIMIZATION, 10);
    }
}
