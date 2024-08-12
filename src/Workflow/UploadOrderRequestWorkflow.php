<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Workflow;

use Setono\SyliusPeakPlugin\Model\UploadOrderRequestInterface;
use Symfony\Component\Workflow\Transition;

final class UploadOrderRequestWorkflow
{
    private const PROPERTY_NAME = 'state';

    final public const NAME = 'setono_sylius_peak__upload_order_request';

    final public const TRANSITION_PROCESS = 'process';

    final public const TRANSITION_UPLOAD = 'upload';

    final public const TRANSITION_FAIL = 'fail';

    final public const TRANSITION_RESET = 'reset';

    private function __construct()
    {
    }

    /**
     * @return array<array-key, string>
     */
    public static function getStates(): array
    {
        return [
            UploadOrderRequestInterface::STATE_PENDING,
            UploadOrderRequestInterface::STATE_PROCESSING,
            UploadOrderRequestInterface::STATE_UPLOADED,
            UploadOrderRequestInterface::STATE_FAILED,
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
                'supports' => UploadOrderRequestInterface::class,
                'initial_marking' => UploadOrderRequestInterface::STATE_PENDING,
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
                [UploadOrderRequestInterface::STATE_PENDING, UploadOrderRequestInterface::STATE_UPLOADED],
                UploadOrderRequestInterface::STATE_PROCESSING,
            ),
            new Transition(
                self::TRANSITION_UPLOAD,
                [UploadOrderRequestInterface::STATE_PROCESSING],
                UploadOrderRequestInterface::STATE_UPLOADED,
            ),
            new Transition(
                self::TRANSITION_FAIL,
                [UploadOrderRequestInterface::STATE_PENDING, UploadOrderRequestInterface::STATE_PROCESSING],
                UploadOrderRequestInterface::STATE_FAILED,
            ),
            new Transition(
                self::TRANSITION_RESET,
                [UploadOrderRequestInterface::STATE_FAILED, UploadOrderRequestInterface::STATE_UPLOADED, UploadOrderRequestInterface::STATE_PROCESSING],
                UploadOrderRequestInterface::STATE_PENDING,
            ),
        ];
    }
}
