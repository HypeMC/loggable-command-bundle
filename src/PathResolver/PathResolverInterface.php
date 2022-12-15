<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\PathResolver;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

interface PathResolverInterface
{
    public function __invoke(array $handlerOptions, LoggableOutputInterface $loggableOutput): string;
}
