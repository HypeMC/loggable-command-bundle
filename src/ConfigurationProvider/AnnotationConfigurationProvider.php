<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Doctrine\Common\Annotations\Reader as AnnotationsReaderInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

final class AnnotationConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * @var AnnotationsReaderInterface
     */
    private $annotationsReader;

    /**
     * @var ContainerBagInterface
     */
    private $containerBag;

    public function __construct(AnnotationsReaderInterface $annotationsReader, ContainerBagInterface $containerBag)
    {
        $this->annotationsReader = $annotationsReader;
        $this->containerBag = $containerBag;
    }

    public function __invoke(LoggableOutputInterface $loggableOutput): array
    {
        $reflection = new \ReflectionObject($loggableOutput);

        /** @var LoggableOutput|null $annotation */
        $annotation = $this->annotationsReader->getClassAnnotation($reflection, LoggableOutput::class);

        if (null === $annotation) {
            return [];
        }

        return $this->containerBag->resolveValue($annotation->getOptions());
    }
}
