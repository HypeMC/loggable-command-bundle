<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests\DependencyInjection\Compiler;

use Bizkit\LoggableCommandBundle\BizkitLoggableCommandBundle;
use Bizkit\LoggableCommandBundle\DependencyInjection\Compiler\ExcludeMonologChannelPass;
use Bizkit\LoggableCommandBundle\Tests\TestCase;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @covers \Bizkit\LoggableCommandBundle\DependencyInjection\Compiler\ExcludeMonologChannelPass
 */
final class ExcludeMonologChannelPassTest extends TestCase
{
    /**
     * @dataProvider handlerChannels
     */
    public function testChannelIsExcludedWhenExpected(?array $channels, ?array $expectedChannels, array $expectedLog): void
    {
        $container = new ContainerBuilder();
        $container->setParameter('kernel.logs_dir', __DIR__);
        $container->setParameter('kernel.environment', 'dev');

        /** @var BundleInterface $bundle */
        foreach ([new MonologBundle(), new BizkitLoggableCommandBundle()] as $bundle) {
            $container->registerExtension($extension = $bundle->getContainerExtension());
            $bundle->build($container);
            $container->loadFromExtension($extension->getAlias());
        }

        $container->loadFromExtension('monolog', [
            'handlers' => [
                'foobar' => [
                    'type' => 'stream',
                    'channels' => $channels,
                ],
            ],
        ]);

        $container->compile();

        /** @var array<string, array{type: string, elements: list<string>}|null> $handlersToChannels */
        $handlersToChannels = $container->getParameter('monolog.handlers_to_channels');
        $this->assertSame($expectedChannels, $handlersToChannels['monolog.handler.foobar']);

        $this->assertSame($expectedLog, array_values(array_filter(
            $container->getCompiler()->getLog(),
            static fn (string $log): bool => str_starts_with($log, ExcludeMonologChannelPass::class),
        )));
    }

    public function handlerChannels(): iterable
    {
        $log = sprintf('%s: Excluded Monolog channel "loggable_output" from the following exclusive handlers "foobar".', ExcludeMonologChannelPass::class);

        yield 'None' => [null, ['type' => 'exclusive', 'elements' => ['loggable_output']], [$log]];
        yield 'Empty array' => [[], ['type' => 'exclusive', 'elements' => ['loggable_output']], [$log]];
        yield 'Inclusive' => [['app'], ['type' => 'inclusive', 'elements' => ['app']], []];
        yield 'Exclusive without exception' => [['!event'], ['type' => 'exclusive', 'elements' => ['event', 'loggable_output']], [$log]];
        yield 'Exclusive with exception' => [['!event', '!!loggable_output'], ['type' => 'exclusive', 'elements' => ['event']], []];
        yield 'Exclusive with only an exception' => [['!!loggable_output'], null, []];
        yield 'Explicitly excluded' => [['!loggable_output'], ['type' => 'exclusive', 'elements' => ['loggable_output']], []];
    }
}
