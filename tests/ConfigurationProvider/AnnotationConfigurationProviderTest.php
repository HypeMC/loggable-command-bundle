<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\AnnotationConfigurationProvider;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyChildLoggableOutputWithAnnotation;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyChildLoggableOutputWithParentAnnotation;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyLoggableOutputWithAnnotation;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyLoggableOutputWithAnnotationAndParam;
use Bizkit\LoggableCommandBundle\Tests\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Doctrine\Common\Annotations\Annotation;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\DocParser;
use Psr\Log\LogLevel;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * @covers \Bizkit\LoggableCommandBundle\ConfigurationProvider\AnnotationConfigurationProvider
 */
final class AnnotationConfigurationProviderTest extends TestCase
{
    protected function setUp(): void
    {
        if (!class_exists(Annotation::class)) {
            $this->markTestSkipped('Doctrine Annotation library is required.');
        }
    }

    public function testProviderReturnsExpectedConfigWhenAnnotationIsFound(): void
    {
        $handlerOptions = ['filename' => 'annotation-test', 'level' => LogLevel::CRITICAL, 'max_files' => 4];

        $provider = $this->createConfigurationProvider(
            $this->createContainerBagWithResolveValueMethodCalled($handlerOptions)
        );

        $this->assertSame(
            $handlerOptions,
            $provider(new DummyLoggableOutputWithAnnotation())
        );
    }

    public function testProviderReturnsExpectedConfigWhenParentAndChildAnnotationsAreFound(): void
    {
        $handlerOptions = ['filename' => 'child-annotation-test', 'level' => LogLevel::CRITICAL, 'max_files' => 4];

        $provider = $this->createConfigurationProvider(
            $this->createContainerBagWithResolveValueMethodCalled($handlerOptions)
        );

        $this->assertSame(
            $handlerOptions,
            $provider(new DummyChildLoggableOutputWithAnnotation())
        );
    }

    public function testProviderReturnsExpectedConfigWhenParentAnnotationIsFound(): void
    {
        $handlerOptions = ['filename' => 'annotation-test', 'level' => LogLevel::CRITICAL, 'max_files' => 4];

        $provider = $this->createConfigurationProvider(
            $this->createContainerBagWithResolveValueMethodCalled($handlerOptions)
        );

        $this->assertSame(
            $handlerOptions,
            $provider(new DummyChildLoggableOutputWithParentAnnotation())
        );
    }

    public function testProviderReturnsEmptyConfigWhenAnnotationIsNotFound(): void
    {
        $provider = $this->createConfigurationProvider(
            $this->createContainerBagWithoutResolveValueMethodCalled()
        );

        $this->assertSame([], $provider(new DummyLoggableOutput()));
    }

    public function testProviderResolvesConfigParameters(): void
    {
        $handlerOptions = ['filename' => 'annotation-test', 'path' => '/var/log/messenger/{filename}.log'];

        $provider = $this->createConfigurationProvider(
            new ContainerBag($container = new Container())
        );

        $container->setParameter('kernel.logs_dir', '/var/log');

        $this->assertSame(
            $handlerOptions,
            $provider(new DummyLoggableOutputWithAnnotationAndParam())
        );
    }

    private function createConfigurationProvider(ContainerBagInterface $containerBag): ConfigurationProviderInterface
    {
        return new AnnotationConfigurationProvider(new AnnotationReader(new DocParser()), $containerBag);
    }

    private function createContainerBagWithResolveValueMethodCalled(array $handlerOptions): ContainerBagInterface
    {
        $containerBag = $this->createMock(ContainerBagInterface::class);
        $containerBag->expects($this->once())
            ->method('resolveValue')
            ->with($handlerOptions)
            ->willReturn($this->returnArgument(0))
        ;

        return $containerBag;
    }

    private function createContainerBagWithoutResolveValueMethodCalled(): ContainerBagInterface
    {
        $containerBag = $this->createMock(ContainerBagInterface::class);
        $containerBag->expects($this->never())->method('resolveValue');

        return $containerBag;
    }
}
