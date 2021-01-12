<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\HandlerFactory\Fixtures;

use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;

interface DummyHandlerInterface extends HandlerInterface
{
    public function pushProcessor($callback): self;

    public function setFormatter(FormatterInterface $formatter): self;
}
