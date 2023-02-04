<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute;

use Doctrine\Common\Annotations\Annotation as Common;

/**
 * Annotation class for @LoggableOutput.
 *
 * @Annotation
 *
 * @Common\Target({"CLASS"})
 * @Common\Attributes({
 *     @Common\Attribute("filename", type="string"),
 *     @Common\Attribute("path", type="string"),
 *     @Common\Attribute("type", type="string"),
 *     @Common\Attribute("bubble", type="bool"),
 *     @Common\Attribute("includeStacktraces", type="bool"),
 *     @Common\Attribute("filePermission", type="int"),
 *     @Common\Attribute("useLocking", type="bool"),
 *     @Common\Attribute("maxFiles", type="int"),
 *     @Common\Attribute("filenameFormat", type="string"),
 *     @Common\Attribute("dateFormat", type="string"),
 *     @Common\Attribute("extraOptions", type="array"),
 * })
 */
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
        array $options = [],
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
        $this->options['filename'] = $options['filename'] ?? $filename;
        $this->options['path'] = $options['path'] ?? $path;
        $this->options['type'] = $options['type'] ?? $type;
        $this->options['level'] = $options['level'] ?? $level;
        $this->options['bubble'] = $options['bubble'] ?? $bubble;
        $this->options['include_stacktraces'] = $options['includeStacktraces'] ?? $includeStacktraces;
        $this->options['file_permission'] = $options['filePermission'] ?? $filePermission;
        $this->options['use_locking'] = $options['useLocking'] ?? $useLocking;
        $this->options['max_files'] = $options['maxFiles'] ?? $maxFiles;
        $this->options['filename_format'] = $options['filenameFormat'] ?? $filenameFormat;
        $this->options['date_format'] = $options['dateFormat'] ?? $dateFormat;
        $this->options['extra_options'] = $options['extraOptions'] ?? $extraOptions;

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
