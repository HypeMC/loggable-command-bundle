<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\PathResolver;

use Bizkit\LoggableCommandBundle\FilenameProvider\FilenameProviderInterface;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

final class DefaultPathResolver implements PathResolverInterface
{
    public function __construct(
        private readonly FilenameProviderInterface $filenameProvider,
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
            $resolvedPath = strtr($resolvedPath, ['{date}' => date($handlerOptions['date_format'])]);
        }

        return $resolvedPath;
    }
}
