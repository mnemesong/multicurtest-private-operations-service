<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceStubs\records;

use Pantagruel74\MulticurtestPrivateOperationsService\records\CurrencySummaryInAccountRecInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\values\AmountInCurrencyValStub;
use Webmozart\Assert\Assert;

class CurrencySummaryInAccountRecStub implements
    CurrencySummaryInAccountRecInterface
{
    private string $id;
    private string $curId;
    private AmountInCurrencyValStub $amount;
    private int $timestamp;

    /**
     * @param string $id
     * @param string $curId
     * @param AmountInCurrencyValStub $amount
     * @param int $timestamp
     */
    public function __construct(
        string $id,
        string $curId,
        AmountInCurrencyValStub $amount,
        int $timestamp
    ) {
        Assert::eq($curId, $amount->getCurId());
        $this->id = $id;
        $this->curId = $curId;
        $this->amount = $amount;
        $this->timestamp = $timestamp;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getCurId(): string
    {
        return $this->curId;
    }

    public function getAmount(): AmountInCurrencyValInterface
    {
        return $this->amount;
    }

    public function getTimestamp(): int
    {
        return $this->timestamp;
    }
}