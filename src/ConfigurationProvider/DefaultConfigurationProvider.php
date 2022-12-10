<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

final class DefaultConfigurationProvider extends AbstractConfigurationProvider
{
    public function __construct(
        private readonly array $handlerOptions,
    ) {
    }

    public function __invoke(LoggableOutputInterface $loggableOutput): array
    {
        return $this->handlerOptions;
    }
}
