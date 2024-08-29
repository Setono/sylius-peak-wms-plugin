<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin\EventListener\Doctrine;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Setono\SyliusPeakPlugin\Factory\UploadProductVariantRequestFactoryInterface;
use Setono\SyliusPeakPlugin\Model\ProductVariantInterface;
use Setono\SyliusPeakPlugin\Workflow\UploadProductVariantRequestWorkflow;
use Sylius\Component\Product\Model\ProductInterface;
use Sylius\Component\Product\Model\ProductVariantInterface as BaseProductVariantInterface;
use Symfony\Component\Workflow\WorkflowInterface;
use Webmozart\Assert\Assert;

final class ProductListener
{
    public function __construct(
        private readonly UploadProductVariantRequestFactoryInterface $uploadProductVariantRequestFactory,
        private readonly WorkflowInterface $uploadProductVariantRequestWorkflow,
    ) {
    }

    public function prePersist(LifecycleEventArgs $eventArgs): void
    {
        $this->handle($eventArgs);
    }

    public function preUpdate(LifecycleEventArgs $eventArgs): void
    {
        $this->handle($eventArgs);
    }

    private function handle(LifecycleEventArgs $eventArgs): void
    {
        $obj = $eventArgs->getObject();
        if (!$obj instanceof BaseProductVariantInterface && !$obj instanceof ProductInterface) {
            return;
        }

        $variants = $obj instanceof ProductInterface ? $obj->getVariants() : [$obj];

        /** @var BaseProductVariantInterface|ProductVariantInterface $variant */
        foreach ($variants as $variant) {
            Assert::isInstanceOf($variant, ProductVariantInterface::class);

            $uploadProductVariantRequest = $variant->getPeakUploadProductVariantRequest() ?? $this->uploadProductVariantRequestFactory->createNew();
            $this->uploadProductVariantRequestWorkflow->apply($uploadProductVariantRequest, UploadProductVariantRequestWorkflow::TRANSITION_RESET);

            $variant->setPeakUploadProductVariantRequest($uploadProductVariantRequest);
        }

        $eventArgs->getObjectManager()->flush();
    }
}
