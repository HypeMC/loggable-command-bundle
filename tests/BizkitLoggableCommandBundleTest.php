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
            $compilerPassIndexes[$compilerPass::class] = $i;
        }

        $this->assertArrayHasKey(LoggerChannelPass::class, $compilerPassIndexes);
        $this->assertArrayHasKey(ExcludeMonologChannelPass::class, $compilerPassIndexes);

        $this->assertGreaterThan(
            $compilerPassIndexes[ExcludeMonologChannelPass::class],
            $compilerPassIndexes[LoggerChannelPass::class],
        );
    }
}
