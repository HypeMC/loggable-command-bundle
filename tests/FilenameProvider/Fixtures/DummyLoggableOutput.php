<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;

class DummyLoggableOutput implements LoggableOutputInterface
{
    use LoggableOutputTrait;
}
