<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\values;

interface AmountInCurrencyValInterface
{
    public function getCurId(): string;
    public function isPositive(): bool;
    public function isZero(): bool;
    public function plus(self $anotherAmount): self;
    public function minus(self $anotherAmount): self;
}