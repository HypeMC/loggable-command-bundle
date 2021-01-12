<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\Tests;

use Bizkit\LoggableCommandBundle\BizkitLoggableCommandBundle;
use Bizkit\LoggableCommandBundle\DependencyInjection\Compiler\ExcludeMonologChannelPass;
use Symfony\Bundle\MonologBundle\DependencyInjection\Compiler\LoggerChannelPass;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @covers \Bizkit\LoggableCommandBundle\BizkitLoggableCommandBundle
 */
final class BizkitLoggableCommandBundleTest extends TestCase
{
    public function testCompilerPassIsRegisteredWithCorrectPriority(): void
    {
        $container = new ContainerBuilder();

        (new MonologBundle())->build($container);
        (new BizkitLoggableCommandBundle())->build($container);

        $compilerPassIndexes = [];
        foreach ($container->getCompilerPassConfig()->getBeforeOptimizationPasses() as $i => $compilerPass) {
            $compilerPassIndexes[\get_class($compilerPass)] = $i;
        }

        $this->assertArrayHasKey(LoggerChannelPass::class, $compilerPassIndexes);
        $this->assertArrayHasKey(ExcludeMonologChannelPass::class, $compilerPassIndexes);

        $this->assertGreaterThan(
            $compilerPassIndexes[ExcludeMonologChannelPass::class],
            $compilerPassIndexes[LoggerChannelPass::class]
        );
    }
}
