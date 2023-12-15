<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;

interface CurrencyConversionManagerInterface
{
    public function convertAmountTo(
        AmountInCurrencyValInterface $amount,
        string $targetCurrency
    ): AmountInCurrencyValInterface;
}