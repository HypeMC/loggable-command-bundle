<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\LoggableOutput;

use Psr\Log\LoggerInterface;

interface LoggableOutputInterface
{
    public function setOutputLogger(LoggerInterface $outputLogger): void;
}
