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

        /** @var array<string, array{type: string, elements: list<string>}|null> $handlersToChannels */
        $handlersToChannels = $container->getParameter('monolog.handlers_to_channels');
        foreach ($handlersToChannels as $id => &$handlersToChannel) {
            if (null === $handlersToChannel) {
                $handlersToChannel = [
                    'type' => 'exclusive',
                    'elements' => [],
                ];
            } elseif ('exclusive' !== $handlersToChannel['type']) {
                continue;
            }

            if (false !== $index = array_search('!'.$monologChannelName, $handlersToChannel['elements'], true)) {
                array_splice($handlersToChannel['elements'], $index, 1);
                if (!$handlersToChannel['elements']) {
                    $handlersToChannel = null;
                }
            } elseif (!\in_array($monologChannelName, $handlersToChannel['elements'], true)) {
                $handlersToChannel['elements'][] = $monologChannelName;
                $exclusiveHandlerNames[] = substr($id, 16);
            }
        }
        $container->setParameter('monolog.handlers_to_channels', $handlersToChannels);

        if ($exclusiveHandlerNames) {
            $container->log($this, sprintf(
                'Excluded Monolog channel "%s" from the following exclusive handlers "%s".',
                $monologChannelName,
                implode('", "', $exclusiveHandlerNames)
            ));
        }
    }
}
