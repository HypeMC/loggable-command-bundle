<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Fixtures;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

final class DummyConfigurationProvider implements ConfigurationProviderInterface
{
    public function __invoke(LoggableOutputInterface $loggableOutput): array
    {
        return [];
    }
}
