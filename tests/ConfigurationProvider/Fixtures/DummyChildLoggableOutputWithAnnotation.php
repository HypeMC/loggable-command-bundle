<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;

/**
 * @LoggableOutput(filename="child-annotation-test")
 */
class DummyChildLoggableOutputWithAnnotation extends DummyLoggableOutputWithAnnotation
{
}
