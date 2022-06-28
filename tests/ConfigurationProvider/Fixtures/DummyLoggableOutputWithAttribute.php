<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;
use Psr\Log\LogLevel;

#[LoggableOutput(filename: 'attribute-test', level: LogLevel::EMERGENCY, bubble: true)]
class DummyLoggableOutputWithAttribute implements LoggableOutputInterface
{
    use LoggableOutputTrait;
}
