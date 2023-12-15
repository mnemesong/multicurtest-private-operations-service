<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceStubs\records;

use Pantagruel74\MulticurtestPrivateOperationsService\records\CurrencyOperationInAccountRequestRecInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\values\AmountInCurrencyValStub;
use Webmozart\Assert\Assert;

class CurrencyOperationInAccountRequestRecStub implements
    CurrencyOperationInAccountRequestRecInterface
{
    private string $id;
    private AmountInCurrencyValStub $amount;
    private bool $isConfirmed;
    private bool $isDeclined;
    private int $timestamp;

    /**
     * @param string $id
     * @param AmountInCurrencyValStub $amount
     * @param bool $isConfirmed
     * @param bool $isDeclined
     * @param int $timestamp
     */
    public function __construct(
        string $id,
        AmountInCurrencyValStub $amount,
        bool $isConfirmed,
        bool $isDeclined,
        int $timestamp
    ) {
        $this->id = $id;
        $this->amount = $amount;
        $this->isConfirmed = $isConfirmed;
        $this->isDeclined = $isDeclined;
        $this->timestamp = $timestamp;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmount(): AmountInCurrencyValInterface
    {
        return $this->amount;
    }

    public function isConfirmed(): bool
    {
        return $this->isConfirmed;
    }

    public function isDeclined(): bool
    {
        return $this->isDeclined;
    }

    /**
     * @return int
     */
    public function getTimestamp(): int
    {
        return $this->timestamp;
    }

    public function asConfirmed(): self
    {
        Assert::false($this->isDeclined);
        $c = clone $this;
        $c->isConfirmed = true;
        return $c;
    }

    public function asDeclined(): self
    {
        Assert::false($this->isConfirmed);
        $c = clone $this;
        $c->isDeclined = true;
        return $c;
    }
}