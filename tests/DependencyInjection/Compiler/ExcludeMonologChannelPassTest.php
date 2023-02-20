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
    public function testChannelIsExcludedWhenExpected(?array $channels, array $expectedChannels, array $expectedLog): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('bizkit_loggable_command.channel_name', 'channel_name');
        $container->setParameter('monolog.handlers_to_channels', ['monolog.handler.foobar' => $channels]);

        (new ExcludeMonologChannelPass())->process($container);

        /** @var array<string, array{type: string, elements: list<string>}> $handlersToChannels */
        $handlersToChannels = $container->getParameter('monolog.handlers_to_channels');
        $this->assertSame($expectedChannels, $handlersToChannels['monolog.handler.foobar']['elements']);

        $this->assertSame($expectedLog, $container->getCompiler()->getLog());
    }

    public function handlerChannels(): iterable
    {
        $log = sprintf('%s: Excluded Monolog channel "channel_name" from the following exclusive handlers "foobar".', ExcludeMonologChannelPass::class);

        yield 'Inclusive' => [['type' => 'inclusive', 'elements' => ['foo', 'bar', 'baz']], ['foo', 'bar', 'baz'], []];
        yield 'Exclusive without exception' => [['type' => 'exclusive', 'elements' => ['foo', 'baz']], ['foo', 'baz', 'channel_name'], [$log]];
        yield 'Exclusive with exception' => [['type' => 'exclusive', 'elements' => ['foo', '!channel_name', 'baz']], ['foo', 'baz'], []];
    }
}
