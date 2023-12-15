<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\values;

interface AmountInCurrencyValInterface
{
    public function getCurId(): string;
    public function getAmountInDecimals(): int;
    public function getAmountInUnits(): float;
    public function getDecimalPosition(): int;
    public function plus(self $anotherAmount): self;
    public function minus(self $anotherAmount): self;
}