<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures;

use Bizkit\LoggableCommandBundle\LoggableOutput\NamedLoggableOutputInterface;
use Bizkit\LoggableCommandBundle\Tests\Fixtures\DummyLoggableOutput;

class DummyNamedLoggableOutput extends DummyLoggableOutput implements NamedLoggableOutputInterface
{
    public function getOutputLogName(): string
    {
        return 'some_custom_name';
    }
}
