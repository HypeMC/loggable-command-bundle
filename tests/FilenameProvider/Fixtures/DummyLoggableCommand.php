<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures;

use Bizkit\LoggableCommandBundle\Command\LoggableCommand;

class DummyLoggableCommand extends LoggableCommand
{
    protected static $defaultName = 'the:command:name';
}
