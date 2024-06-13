# Sylius plugin for Peak WMS

[![Latest Version][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE)
[![Build Status][ico-github-actions]][link-github-actions]
[![Code Coverage][ico-code-coverage]][link-code-coverage]

## Installation

```bash
composer require setono/sylius-peak-wms-plugin
```

### Add plugin class to your `bundles.php`

Make sure you add it before `SyliusGridBundle`, otherwise you'll get
`You have requested a non-existent parameter "setono_sylius_peak_wms.model.remote_event.class".` exception.

```php
<?php
$bundles = [
    // ...
    Setono\SyliusPeakWMSPlugin\SetonoSyliusPeakWMSPlugin::class => ['all' => true],
    Sylius\Bundle\GridBundle\SyliusGridBundle::class => ['all' => true],
    // ...
];
```

### Import routing

```yaml
# config/routes/setono_sylius_peak_wms.yaml
setono_sylius_peak_wms:
    resource: "@SetonoSyliusPeakWMSPlugin/Resources/config/routes.yaml"
```

or if your app doesn't use locales:

```yaml
# config/routes/setono_sylius_peak_wms.yaml
setono_sylius_peak_wms:
    resource: "@SetonoSyliusPeakWMSPlugin/Resources/config/routes_no_locale.yaml"
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

use Setono\SyliusPeakWMSPlugin\Model\OrderInterface as PeakWMSOrderInterface;
use Setono\SyliusPeakWMSPlugin\Model\OrderTrait as PeakWMSOrderTrait;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 *
 * @ORM\Table(name="sylius_order")
 */
class Order extends BaseOrder implements PeakWMSOrderInterface
{
    use PeakWMSOrderTrait;
}
```

### Update your database:

```bash
php bin/console doctrine:migrations:diff
php bin/console doctrine:migrations:migrate
```

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

[link-packagist]: https://packagist.org/packages/setono/sylius-peak-wms-plugin
[link-github-actions]: https://github.com/Setono/sylius-peak-wms-plugin/actions
[link-code-coverage]: https://codecov.io/gh/Setono/sylius-peak-wms-plugin
