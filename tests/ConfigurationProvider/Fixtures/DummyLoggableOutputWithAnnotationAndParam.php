<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;

/**
 * @LoggableOutput(filename="annotation-test", path="%kernel.logs_dir%/messenger/{filename}.log")
 */
class DummyLoggableOutputWithAnnotationAndParam implements LoggableOutputInterface
{
    use LoggableOutputTrait;
}
