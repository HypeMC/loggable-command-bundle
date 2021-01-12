<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

final class MergedConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * @var ConfigurationProviderInterface[]
     */
    private $configurationProviders;

    public function __construct(iterable $configurationProviders)
    {
        $this->configurationProviders = $configurationProviders;
    }

    public function __invoke(LoggableOutputInterface $loggableOutput): array
    {
        $mergedConfiguration = [];
        $extraOptions = [];

        foreach ($this->configurationProviders as $configurationProvider) {
            if ([] !== $configuration = $configurationProvider($loggableOutput)) {
                if (!empty($configuration['extra_options'])) {
                    $extraOptions += $configuration['extra_options'];
                    unset($configuration['extra_options']);
                }
                $mergedConfiguration += $configuration;
            }
        }

        $mergedConfiguration['extra_options'] = $extraOptions;

        return $mergedConfiguration;
    }
}
