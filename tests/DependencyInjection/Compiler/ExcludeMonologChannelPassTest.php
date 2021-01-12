<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Compiler;

use Bizkit\LoggableCommandBundle\DependencyInjection\Compiler\ExcludeMonologChannelPass;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Bizkit\LoggableCommandBundle\DependencyInjection\Compiler\ExcludeMonologChannelPass
 */
final class ExcludeMonologChannelPassTest extends TestCase
{
    /**
     * @dataProvider handlerChannels
     */
    public function testChannelIsExcludedWhenExpected(string $type, array $elements, array $expectedElements): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('bizkit_loggable_command.channel_name', 'channel_name');
        $container->setParameter('monolog.handlers_to_channels', [
            'foobar' => ['type' => $type, 'elements' => $elements],
        ]);

        (new ExcludeMonologChannelPass())->process($container);

        $this->assertSame($expectedElements, $container->getParameter('monolog.handlers_to_channels')['foobar']['elements']);
    }

    public function handlerChannels(): iterable
    {
        yield 'Inclusive' => ['inclusive', ['foo', 'bar', 'baz'], ['foo', 'bar', 'baz']];
        yield 'Exclusive without exception' => ['exclusive', ['foo', 'baz'], ['foo', 'baz', 'channel_name']];
        yield 'Exclusive with exception' => ['exclusive', ['foo', '!channel_name', 'baz'], ['foo', 'baz']];
    }
}
