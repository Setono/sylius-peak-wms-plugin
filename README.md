# Sylius plugin for Peak WMS

[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Code Coverage][ico-code-coverage]][link-code-coverage]
[![Mutation testing][ico-infection]][link-infection]

## Installation

```bash
composer require setono/sylius-peak-wms-plugin
```

### Add plugin class to your `bundles.php`

Make sure you add it before `SyliusGridBundle`, otherwise you'll get
`You have requested a non-existent parameter "setono_sylius_peak.model.upload_order_request.class".` exception.

```php
<?php
$bundles = [
    // ...
    Setono\SyliusPeakPlugin\SetonoSyliusPeakPlugin::class => ['all' => true],
    Sylius\Bundle\GridBundle\SyliusGridBundle::class => ['all' => true],
    // ...
];
```

### Import routing

```yaml
# config/routes/setono_sylius_peak.yaml
setono_sylius_peak:
    resource: "@SetonoSyliusPeakPlugin/Resources/config/routes.yaml"
```

or if your app doesn't use locales:

```yaml
# config/routes/setono_sylius_peak.yaml
setono_sylius_peak:
    resource: "@SetonoSyliusPeakPlugin/Resources/config/routes_no_locale.yaml"
```

### Add environment variables

Add the following variables to your `.env` file:

```dotenv
###> setono/sylius-peak-wms-plugin ###
PEAK_WMS_API_KEY=YOUR_API_KEY
###< setono/sylius-peak-wms-plugin ###
```

### Extend entities

#### `Order` entity

```php
<?php

# src/Entity/Order/Order.php

declare(strict_types=1);

namespace App\Entity\Order;

use Setono\SyliusPeakPlugin\Model\OrderInterface as PeakOrderInterface;
use Setono\SyliusPeakPlugin\Model\OrderTrait as PeakOrderTrait;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="sylius_order")
 */
class Order extends BaseOrder implements PeakOrderInterface
{
    use PeakOrderTrait;
}
```

#### `ProductVariant` entity

```php
<?php

# src/Entity/Product/ProductVariant.php

declare(strict_types=1);

namespace App\Entity\Order;

use Doctrine\ORM\Mapping as ORM;
use Setono\SyliusPeakPlugin\Model\ProductVariantInterface as PeakProductVariantInterface;
use Setono\SyliusPeakPlugin\Model\ProductVariantTrait as PeakProductVariantTrait;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="sylius_product_variant")
 */
class ProductVariant extends BaseProductVariant implements PeakProductVariantInterface
{
    use PeakProductVariantTrait;
}
```

### Update your database

```shell
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

### Add jobs to your cron

```bash
# Will process the orders that are ready to be sent to Peak WMS
php bin/console setono:sylius-peak-wms:process-upload-order-requests

# Will create upload product variant requests for all product variants
php bin/console setono:sylius-peak-wms:create-upload-product-variant-requests

# Will process the upload product variant requests
php bin/console setono:sylius-peak-wms:process-upload-product-variant-requests

# Will update the inventory in Sylius based on the inventory in Peak WMS
php bin/console setono:sylius-peak-wms:update-inventory
```

### Register webhooks

To receive stock adjustments and order status updates from Peak WMS, you need to register webhooks in Peak WMS.

Do this by running the following command:

```shell
php bin/console setono:sylius-peak-wms:register-webhooks
```

**NOTICE** That you also need to enable the sending of webhooks inside the Peak interface.

## Development

```shell
(cd tests/Application && yarn install)
(cd tests/Application && yarn build)
(cd tests/Application && bin/console assets:install)

(cd tests/Application && bin/console doctrine:database:create)
(cd tests/Application && bin/console doctrine:schema:create)

(cd tests/Application && bin/console sylius:fixtures:load -n)

(cd tests/Application && symfony serve -d)

vendor/bin/expose token <your expose token>
vendor/bin/expose default-server free # If you are not paying for Expose
vendor/bin/expose share https://127.0.0.1:8000
```

[ico-version]: https://poser.pugx.org/setono/sylius-peak-wms-plugin/v/stable
[ico-license]: https://poser.pugx.org/setono/sylius-peak-wms-plugin/license
[ico-github-actions]: https://github.com/Setono/sylius-peak-wms-plugin/actions/workflows/build.yaml/badge.svg
[ico-code-coverage]: https://codecov.io/gh/Setono/sylius-peak-wms-plugin/branch/master/graph/badge.svg
[ico-infection]: https://img.shields.io/endpoint?style=flat&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2FSetono%2Fsylius-peak-wms-plugin%2Fmaster

[link-packagist]: https://packagist.org/packages/setono/sylius-peak-wms-plugin
[link-github-actions]: https://github.com/Setono/sylius-peak-wms-plugin/actions
[link-code-coverage]: https://codecov.io/gh/Setono/sylius-peak-wms-plugin
[link-infection]: https://dashboard.stryker-mutator.io/reports/github.com/Setono/sylius-peak-wms-plugin/master
