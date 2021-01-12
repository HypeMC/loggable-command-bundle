<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;
use Monolog\Logger;

#[LoggableOutput(filename: 'attribute-test', level: Logger::EMERGENCY, bubble: true)]
final class DummyLoggableOutputWithAttribute implements LoggableOutputInterface
{
    use LoggableOutputTrait;
}
