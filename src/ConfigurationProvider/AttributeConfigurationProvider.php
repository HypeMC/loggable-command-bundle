<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;

final class AttributeConfigurationProvider implements ConfigurationProviderInterface
{
    public function __invoke(LoggableOutputInterface $loggableOutput): array
    {
        $reflectionObject = new \ReflectionObject($loggableOutput);

        $reflectionAttributes = $reflectionObject->getAttributes(LoggableOutput::class);

        if (null === $reflectionAttribute = $reflectionAttributes[0] ?? null) {
            return [];
        }

        /** @var LoggableOutput $attribute */
        $attribute = $reflectionAttribute->newInstance();

        return $attribute->getOptions();
    }
}
