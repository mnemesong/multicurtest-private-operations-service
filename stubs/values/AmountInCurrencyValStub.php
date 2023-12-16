<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceStubs\values;

use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;
use Webmozart\Assert\Assert;

class AmountInCurrencyValStub implements AmountInCurrencyValInterface
{
    protected string $curId;
    protected int $amount;

    /**
     * @param string $curId
     * @param int $amount
     */
    public function __construct(string $curId, int $amount)
    {
        $this->curId = $curId;
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    public function reverse(): self
    {
        return new self($this->curId, (-1) * $this->amount);
    }

    public function getCurId(): string
    {
        return $this->curId;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function plus(
        AmountInCurrencyValInterface $anotherAmount
    ): AmountInCurrencyValInterface {
        Assert::isAOf($anotherAmount, self::class);
        /* @var self $anotherAmount */
        return new self($this->curId, $this->amount + $anotherAmount->amount);
    }

    public function minus(
        AmountInCurrencyValInterface $anotherAmount
    ): AmountInCurrencyValInterface {
        Assert::isAOf($anotherAmount, self::class);
        /* @var self $anotherAmount */
        return new self($this->curId, $this->amount - $anotherAmount->amount);
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }
}