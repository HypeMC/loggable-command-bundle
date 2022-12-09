<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final class LoggableOutput
{
    /**
     * @var array
     */
    private $options = [];

    /**
     * @param string|int|null $level
     */
    public function __construct(
        ?string $filename = null,
        ?string $path = null,
        ?string $type = null,
        $level = null,
        ?bool $bubble = null,
        ?bool $includeStacktraces = null,
        ?int $filePermission = null,
        ?bool $useLocking = null,
        ?int $maxFiles = null,
        ?string $filenameFormat = null,
        ?string $dateFormat = null,
        ?array $extraOptions = null
    ) {
        $this->options['filename'] = $filename;
        $this->options['path'] = $path;
        $this->options['type'] = $type;
        $this->options['level'] = $level;
        $this->options['bubble'] = $bubble;
        $this->options['include_stacktraces'] = $includeStacktraces;
        $this->options['file_permission'] = $filePermission;
        $this->options['use_locking'] = $useLocking;
        $this->options['max_files'] = $maxFiles;
        $this->options['filename_format'] = $filenameFormat;
        $this->options['date_format'] = $dateFormat;
        $this->options['extra_options'] = $extraOptions;

        self::validateLevel($this->options['level']);

        $this->options = array_filter($this->options, static function ($value): bool {
            return null !== $value;
        });
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    private static function validateLevel($level): void
    {
        if (isset($level) && !(\is_string($level) || \is_int($level))) {
            throw new \TypeError(sprintf(
                '%s::__construct: Level must be a string or an integer, "%s" given.',
                __CLASS__,
                \is_object($level) ? \get_class($level) : \gettype($level)
            ));
        }
    }
}
