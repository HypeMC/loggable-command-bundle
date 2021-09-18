<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

final class AttributeConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * @var ContainerBagInterface
     */
    private $containerBag;

    public function __construct(ContainerBagInterface $containerBag)
    {
        $this->containerBag = $containerBag;
    }

    public function __invoke(LoggableOutputInterface $loggableOutput): array
    {
        $reflectionObject = new \ReflectionObject($loggableOutput);

        $reflectionAttributes = $reflectionObject->getAttributes(LoggableOutput::class);

        if (null === $reflectionAttribute = $reflectionAttributes[0] ?? null) {
            return [];
        }

        /** @var LoggableOutput $attribute */
        $attribute = $reflectionAttribute->newInstance();

        return $this->containerBag->resolveValue($attribute->getOptions());
    }
}
