<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;

interface CurrencyManagerInterface
{
    public function getZeroForCurrency(
        string $curId
    ): AmountInCurrencyValInterface;

    public function convertAmountTo(
        AmountInCurrencyValInterface $amount,
        string $targetCurrency
    ): AmountInCurrencyValInterface;
}