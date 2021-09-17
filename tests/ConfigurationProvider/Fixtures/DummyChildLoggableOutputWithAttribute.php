<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;

#[LoggableOutput(filename: 'child-attribute-test')]
class DummyChildLoggableOutputWithAttribute extends DummyLoggableOutputWithAttribute
{
}
