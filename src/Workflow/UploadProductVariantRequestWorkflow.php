<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\Workflow;

use Setono\SyliusPeakPlugin\Model\UploadProductVariantRequestInterface;
use Symfony\Component\Workflow\Transition;

final class UploadProductVariantRequestWorkflow
{
    private const PROPERTY_NAME = 'state';

    final public const NAME = 'setono_sylius_peak__upload_product_variant_request';

    final public const TRANSITION_PROCESS = 'process';

    final public const TRANSITION_UPLOAD = 'upload';

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
            UploadProductVariantRequestInterface::STATE_PENDING,
            UploadProductVariantRequestInterface::STATE_PROCESSING,
            UploadProductVariantRequestInterface::STATE_UPLOADED,
            UploadProductVariantRequestInterface::STATE_FAILED,
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
                'supports' => UploadProductVariantRequestInterface::class,
                'initial_marking' => UploadProductVariantRequestInterface::STATE_PENDING,
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
                [UploadProductVariantRequestInterface::STATE_PENDING, UploadProductVariantRequestInterface::STATE_UPLOADED],
                UploadProductVariantRequestInterface::STATE_PROCESSING,
            ),
            new Transition(
                self::TRANSITION_UPLOAD,
                [UploadProductVariantRequestInterface::STATE_PROCESSING],
                UploadProductVariantRequestInterface::STATE_UPLOADED,
            ),
            new Transition(
                self::TRANSITION_FAIL,
                [UploadProductVariantRequestInterface::STATE_PENDING, UploadProductVariantRequestInterface::STATE_PROCESSING],
                UploadProductVariantRequestInterface::STATE_FAILED,
            ),
        ];
    }
}
