<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\records;

use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;

interface CurrencyOperationInAccountRequestRecInterface
{
    public function getId(): string;
    public function getAmount(): AmountInCurrencyValInterface;
    public function isConfirmed(): bool;
    public function isDeclined(): bool;
}