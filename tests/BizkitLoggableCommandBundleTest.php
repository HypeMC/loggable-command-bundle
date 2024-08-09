<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests;

use Bizkit\LoggableCommandBundle\BizkitLoggableCommandBundle;
use Bizkit\LoggableCommandBundle\DependencyInjection\Compiler\ExcludeMonologChannelPass;
use Symfony\Bundle\MonologBundle\DependencyInjection\Compiler\LoggerChannelPass;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;

/**
 * @covers \Bizkit\LoggableCommandBundle\BizkitLoggableCommandBundle
 */
final class BizkitLoggableCommandBundleTest extends TestCase
{
    public function testCompilerPassIsRegisteredWithCorrectPriority(): void
    {
        $container = new ContainerBuilder();

        /** @var BundleInterface $bundle */
        foreach ([new MonologBundle(), new BizkitLoggableCommandBundle()] as $bundle) {
            $bundle->build($container);
        }

        $compilerPassIndexes = [];
        foreach ($container->getCompilerPassConfig()->getBeforeOptimizationPasses() as $i => $compilerPass) {
            $compilerPassIndexes[\get_class($compilerPass)] = $i;
        }

        self::assertArrayHasKey(LoggerChannelPass::class, $compilerPassIndexes);
        self::assertArrayHasKey(ExcludeMonologChannelPass::class, $compilerPassIndexes);

        self::assertGreaterThan(
            $compilerPassIndexes[ExcludeMonologChannelPass::class],
            $compilerPassIndexes[LoggerChannelPass::class]
        );
    }
}
