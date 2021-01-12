<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\Attribute\LoggableOutput;
use Bizkit\LoggableCommandBundle\LoggableOutput\LoggableOutputInterface;
use Doctrine\Common\Annotations\Reader as AnnotationsReaderInterface;

final class AnnotationConfigurationProvider implements ConfigurationProviderInterface
{
    /**
     * @var AnnotationsReaderInterface
     */
    private $annotationsReader;

    public function __construct(AnnotationsReaderInterface $annotationsReader)
    {
        $this->annotationsReader = $annotationsReader;
    }

    public function __invoke(LoggableOutputInterface $loggableOutput): array
    {
        $reflection = new \ReflectionObject($loggableOutput);

        /** @var LoggableOutput|null $annotation */
        $annotation = $this->annotationsReader->getClassAnnotation($reflection, LoggableOutput::class);

        if (null === $annotation) {
            return [];
        }

        return $annotation->getOptions();
    }
}
