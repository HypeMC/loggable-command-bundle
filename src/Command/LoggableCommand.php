<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Command;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputTrait;
use Symfony\Component\Console\Command\Command;

class LoggableCommand extends Command implements LoggableOutputInterface
{
    use LoggableOutputTrait;
}
