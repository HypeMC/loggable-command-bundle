<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\PathResolver;

use Bizkit\LoggableCommandBundle\FilenameProvider\FilenameProviderInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Psr\Clock\ClockInterface;

final class DefaultPathResolver implements PathResolverInterface
{
    public function __construct(
        private readonly FilenameProviderInterface $filenameProvider,
        private readonly ?ClockInterface $clock = null,
    ) {
    }

    public function __invoke(array $handlerOptions, LoggableOutputInterface $loggableOutput): string
    {
        $resolvedPath = $handlerOptions['path'];

        if (str_contains($resolvedPath, '{filename}')) {
            $filename = $handlerOptions['filename'] ?? ($this->filenameProvider)($loggableOutput);
            $resolvedPath = strtr($resolvedPath, ['{filename}' => $filename]);
        }

        if (str_contains($resolvedPath, '{date}')) {
            $date = $this->clock?->now()->format($handlerOptions['date_format']) ?? date($handlerOptions['date_format']);
            $resolvedPath = strtr($resolvedPath, ['{date}' => $date]);
        }

        return $resolvedPath;
    }
}
