<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\LoggableOutput;

use Psr\Log\LoggerInterface;

trait LoggableOutputTrait
{
    protected LoggerInterface $outputLogger;

    public function setOutputLogger(LoggerInterface $outputLogger): void
    {
        $this->outputLogger = $outputLogger;
    }
}
