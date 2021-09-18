<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider;

use Bizkit\LoggableCommandBundle\ConfigurationProvider\AttributeConfigurationProvider;
use Bizkit\LoggableCommandBundle\ConfigurationProvider\ConfigurationProviderInterface;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyLoggableOutput;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyLoggableOutputWithAttribute;
use Bizkit\LoggableCommandBundle\Tests\ConfigurationProvider\Fixtures\DummyLoggableOutputWithAttributeAndParam;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBag;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;

/**
 * @requires PHP >= 8.0
 *
 * @covers \Bizkit\LoggableCommandBundle\ConfigurationProvider\AttributeConfigurationProvider
 */
final class AttributeConfigurationProviderTest extends TestCase
{
    public function testProviderReturnsExpectedConfigWhenAttributeIsFound(): void
    {
        $handlerOptions = ['filename' => 'attribute-test', 'level' => Logger::EMERGENCY, 'bubble' => true];

        $provider = $this->createConfigurationProvider(
            $this->createContainerBagWithResolveValueMethodCalled($handlerOptions)
        );

        $this->assertSame(
            $handlerOptions,
            $provider(new DummyLoggableOutputWithAttribute())
        );
    }

    public function testProviderReturnsEmptyConfigWhenAttributeIsNotFound(): void
    {
        $provider = $this->createConfigurationProvider(
            $this->createContainerBagWithoutResolveValueMethodCalled()
        );

        $this->assertSame([], $provider(new DummyLoggableOutput()));
    }

    public function testProviderResolvesConfigParameters(): void
    {
        $handlerOptions = ['filename' => 'attribute-test', 'path' => '/var/log/messenger/{filename}.log'];

        $provider = $this->createConfigurationProvider(
            new ContainerBag($container = new Container())
        );

        $container->setParameter('kernel.logs_dir', '/var/log');

        $this->assertSame(
            $handlerOptions,
            $provider(new DummyLoggableOutputWithAttributeAndParam())
        );
    }

    private function createConfigurationProvider(ContainerBagInterface $containerBag): ConfigurationProviderInterface
    {
        return new AttributeConfigurationProvider($containerBag);
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
