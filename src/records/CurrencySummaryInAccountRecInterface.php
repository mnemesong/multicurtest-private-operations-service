<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\records;

use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;

interface CurrencySummaryInAccountRecInterface
{
    public function getId(): string;
    public function getCurId(): string;
    public function getAmount(): AmountInCurrencyValInterface;
    public function getTimestamp(): int;
}