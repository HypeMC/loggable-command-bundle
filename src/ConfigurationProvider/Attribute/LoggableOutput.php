<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class LoggableOutput
{
    public readonly array $options;

    public function __construct(
        ?string $filename = null,
        ?string $path = null,
        ?string $type = null,
        string|int|null $level = null,
        ?bool $bubble = null,
        ?bool $includeStacktraces = null,
        ?int $filePermission = null,
        ?bool $useLocking = null,
        ?int $maxFiles = null,
        ?string $filenameFormat = null,
        ?string $dateFormat = null,
        ?array $extraOptions = null,
    ) {
        $this->options = array_filter([
            'filename' => $filename,
            'path' => $path,
            'type' => $type,
            'level' => $level,
            'bubble' => $bubble,
            'include_stacktraces' => $includeStacktraces,
            'file_permission' => $filePermission,
            'use_locking' => $useLocking,
            'max_files' => $maxFiles,
            'filename_format' => $filenameFormat,
            'date_format' => $dateFormat,
            'extra_options' => $extraOptions,
        ], static fn ($value): bool => null !== $value);
    }
}
