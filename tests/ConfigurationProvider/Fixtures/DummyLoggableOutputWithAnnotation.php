<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;
use Monolog\Logger;

/**
 * @LoggableOutput(filename="annotation-test", level=Logger::CRITICAL, maxFiles=4)
 */
class DummyLoggableOutputWithAnnotation implements LoggableOutputInterface
{
    use LoggableOutputTrait;
}
