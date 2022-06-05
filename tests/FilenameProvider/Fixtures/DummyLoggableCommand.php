<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\FilenameProvider\Fixtures;

use Bizkit\LoggableCommandBundle\Command\LoggableCommand;
use Symfony\Component\Console\Attribute\AsCommand;

#[AsCommand(name: self::DEFAULT_NAME)]
class DummyLoggableCommand extends LoggableCommand
{
    private const DEFAULT_NAME = 'the:command:name';
    protected static $defaultName = self::DEFAULT_NAME;
}
