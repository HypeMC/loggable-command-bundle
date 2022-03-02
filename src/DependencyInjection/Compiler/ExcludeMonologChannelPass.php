<?php

declare(strict_types=1);

namespace Bizkit\LoggableCommandBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Needs to happen before {@see LoggerChannelPass}.
 */
final class ExcludeMonologChannelPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $monologChannelName = $container->getParameter('bizkit_loggable_command.channel_name');

        /** @var string[] $exclusiveHandlerNames */
        $exclusiveHandlerNames = [];

        /** @var array<string, array{type: string, elements: list<string>}> $handlersToChannels */
        $handlersToChannels = $container->getParameter('monolog.handlers_to_channels');
        foreach ($handlersToChannels as $id => &$handlersToChannel) {
            if ('exclusive' !== $handlersToChannel['type']) {
                continue;
            }

            if (false !== $index = array_search('!'.$monologChannelName, $handlersToChannel['elements'], true)) {
                array_splice($handlersToChannel['elements'], $index, 1);
            } else {
                $handlersToChannel['elements'][] = $monologChannelName;
                $exclusiveHandlerNames[] = substr($id, 16);
            }
        }
        $container->setParameter('monolog.handlers_to_channels', $handlersToChannels);

        $container->log($this, sprintf(
            'Excluded Monolog channel "%s" from the following exclusive handlers "%s".',
            $monologChannelName,
            implode('", "', $exclusiveHandlerNames)
        ));
    }
}
