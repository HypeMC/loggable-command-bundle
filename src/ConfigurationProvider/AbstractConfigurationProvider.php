<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

abstract class AbstractConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * Appends configuration options from the second configuration to the first configuration
     * while not overwriting the options from the first configuration.
     */
    protected static function mergeConfigurations(array $firstConfiguration, array $secondConfiguration): array
    {
        $extraOptions = $firstConfiguration['extra_options'] ?? [];
        unset($firstConfiguration['extra_options']);

        if (isset($secondConfiguration['extra_options'])) {
            $extraOptions += $secondConfiguration['extra_options'];
            unset($secondConfiguration['extra_options']);
        }

        $firstConfiguration += $secondConfiguration;

        if ($extraOptions) {
            $firstConfiguration['extra_options'] = $extraOptions;
        }

        return $firstConfiguration;
    }
}
