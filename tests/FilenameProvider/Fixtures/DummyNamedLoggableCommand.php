<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures;

use Bizkit\LoggableCommandBundle\LoggableOutput\NamedLoggableOutputInterface;

class DummyNamedLoggableCommand extends DummyLoggableCommand implements NamedLoggableOutputInterface
{
    public function getOutputLogName(): string
    {
        return 'this_has_precedence';
    }
}
