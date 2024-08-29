<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Workflow;

use Setono\SyliusPeakPlugin\Model\InventoryUpdateInterface;
use Symfony\Component\Workflow\Transition;

final class InventoryUpdateWorkflow
{
    private const PROPERTY_NAME = 'state';

    final public const NAME = 'setono_sylius_peak__inventory_update';

    final public const TRANSITION_PROCESS = 'process';

    final public const TRANSITION_COMPLETE = 'complete';

    final public const TRANSITION_RESET = 'reset';

    final public const TRANSITION_FAIL = 'fail';

    private function __construct()
    {
    }

    /**
     * @return array<array-key, string>
     */
    public static function getStates(): array
    {
        return [
            InventoryUpdateInterface::STATE_PENDING,
            InventoryUpdateInterface::STATE_PROCESSING,
            InventoryUpdateInterface::STATE_COMPLETED,
            InventoryUpdateInterface::STATE_FAILED,
        ];
    }

    public static function getConfig(): array
    {
        $transitions = [];
        foreach (self::getTransitions() as $transition) {
            $transitions[$transition->getName()] = [
                'from' => $transition->getFroms(),
                'to' => $transition->getTos(),
            ];
        }

        return [
            self::NAME => [
                'type' => 'state_machine',
                'marking_store' => [
                    'type' => 'method',
                    'property' => self::PROPERTY_NAME,
                ],
                'supports' => InventoryUpdateInterface::class,
                'initial_marking' => InventoryUpdateInterface::STATE_PENDING,
                'places' => self::getStates(),
                'transitions' => $transitions,
            ],
        ];
    }

    /**
     * @return array<array-key, Transition>
     */
    public static function getTransitions(): array
    {
        return [
            new Transition(
                self::TRANSITION_PROCESS,
                InventoryUpdateInterface::STATE_PENDING,
                InventoryUpdateInterface::STATE_PROCESSING,
            ),
            new Transition(
                self::TRANSITION_COMPLETE,
                InventoryUpdateInterface::STATE_PROCESSING,
                InventoryUpdateInterface::STATE_COMPLETED,
            ),
            new Transition(
                self::TRANSITION_RESET,
                [InventoryUpdateInterface::STATE_PENDING, InventoryUpdateInterface::STATE_COMPLETED, InventoryUpdateInterface::STATE_FAILED],
                InventoryUpdateInterface::STATE_PENDING,
            ),
            new Transition(
                self::TRANSITION_FAIL,
                [InventoryUpdateInterface::STATE_PENDING, InventoryUpdateInterface::STATE_PROCESSING],
                InventoryUpdateInterface::STATE_FAILED,
            ),
        ];
    }
}
