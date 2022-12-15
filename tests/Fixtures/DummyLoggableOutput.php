<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\Fixtures;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;
use Psr\Log\LoggerInterface;

class DummyLoggableOutput implements LoggableOutputInterface
{
    use LoggableOutputTrait;

    public function getOutputLogger(): LoggerInterface
    {
        return $this->outputLogger;
    }
}
