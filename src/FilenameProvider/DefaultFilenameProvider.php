<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\FilenameProvider;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\NamedLoggableOutputInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\Container;

final class DefaultFilenameProvider implements FilenameProviderInterface
{
    public function __invoke(LoggableOutputInterface $loggableOutput): string
    {
        if ($loggableOutput instanceof NamedLoggableOutputInterface) {
            return $loggableOutput->getOutputLogName();
        }

        if ($loggableOutput instanceof Command && null !== $name = $loggableOutput->getName()) {
            return str_replace([':', '-'], '_', $name);
        }

        $classBaseName = substr(strrchr(\get_class($loggableOutput), '\\'), 1);

        return Container::underscore($classBaseName);
    }
}
