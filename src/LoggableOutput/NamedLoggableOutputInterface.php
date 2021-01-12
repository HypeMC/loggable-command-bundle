<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\LoggableOutput;

interface NamedLoggableOutputInterface extends LoggableOutputInterface
{
    public function getOutputLogName(): string;
}
