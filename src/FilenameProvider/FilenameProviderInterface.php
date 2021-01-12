<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\FilenameProvider;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

interface FilenameProviderInterface
{
    public function __invoke(LoggableOutputInterface $loggableOutput): string;
}
