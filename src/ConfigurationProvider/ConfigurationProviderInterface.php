<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

interface ConfigurationProviderInterface
{
    public function __invoke(LoggableOutputInterface $loggableOutput): array;
}
