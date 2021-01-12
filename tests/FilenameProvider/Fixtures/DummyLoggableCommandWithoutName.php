<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures;

class DummyLoggableCommandWithoutName extends DummyLoggableCommand
{
    protected static $defaultName;
}
