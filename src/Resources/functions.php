<?php

declare(strict_types=1);

namespace Setono\SyliusPeakPlugin;

/** @psalm-suppress UndefinedClass,MixedArgument */
if (!\function_exists(formatAmount::class)) {
    function formatAmount(int $amount): float
    {
        return round($amount / 100, 2);
    }
}
