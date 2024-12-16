<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventSubscriber;

use Setono\SyliusPeakPlugin\Factory\UploadProductVariantRequestFactoryInterface;
use Setono\SyliusPeakPlugin\Model\ProductVariantInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadProductVariantRequestWorkflow;
use Sylius\Bundle\ResourceBundle\Event\ResourceControllerEvent;
use Sylius\Component\Core\Model\ProductInterface;
use Sylius\Component\Core\Model\ProductVariantInterface as BaseProductVariantInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Webmozart\Assert\Assert;

final class CreateUploadProductVariantRequestSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly UploadProductVariantRequestFactoryInterface $uploadProductVariantRequestFactory,
        private readonly WorkflowInterface $uploadProductVariantRequestWorkflow,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'sylius.product.pre_create' => 'handle',
            'sylius.product.pre_update' => 'handle',
            'sylius.product_variant.pre_create' => 'handle',
            'sylius.product_variant.pre_update' => 'handle',
        ];
    }

    public function handle(ResourceControllerEvent $event): void
    {
        /** @var mixed $obj */
        $obj = $event->getSubject();

        $variants = match (true) {
            $obj instanceof ProductInterface => $obj->getVariants(),
            $obj instanceof ProductVariantInterface => [$obj],
            default => [],
        };

        if (!is_countable($variants)) {
            throw new \LogicException('The variants must be iterable and countable.');
        }

        if (count($variants) === 0) {
            return;
        }

        /** @var BaseProductVariantInterface|ProductVariantInterface $variant */
        foreach ($variants as $variant) {
            Assert::isInstanceOf($variant, ProductVariantInterface::class);

            $uploadProductVariantRequest = $variant->getPeakUploadProductVariantRequest() ?? $this->uploadProductVariantRequestFactory->createNew();

            if ($this->uploadProductVariantRequestWorkflow->can($uploadProductVariantRequest, UploadProductVariantRequestWorkflow::TRANSITION_RESET)) {
                $this->uploadProductVariantRequestWorkflow->apply(
                    $uploadProductVariantRequest,
                    UploadProductVariantRequestWorkflow::TRANSITION_RESET,
                );
            }

            $variant->setPeakUploadProductVariantRequest($uploadProductVariantRequest);
        }
    }
}
