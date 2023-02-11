<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\WithLoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;

#[WithLoggableOutput(filename: 'attribute-test', path: '%kernel.logs_dir%/messenger/{filename}.log')]
class DummyLoggableOutputWithAttributeAndParam implements LoggableOutputInterface
{
    use LoggableOutputTrait;
}
