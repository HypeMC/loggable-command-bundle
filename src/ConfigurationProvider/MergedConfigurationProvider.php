<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

final class MergedConfigurationProvider extends AbstractConfigurationProvider
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

        foreach ($this->configurationProviders as $configurationProvider) {
            if ([] !== $configuration = $configurationProvider($loggableOutput)) {
                $mergedConfiguration = self::mergeConfigurations($mergedConfiguration, $configuration);
            }
        }

        return $mergedConfiguration;
    }
}
