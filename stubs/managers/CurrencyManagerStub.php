<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceStubs\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\managers\CurrencyManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\values\AmountInCurrencyValStub;
use Webmozart\Assert\Assert;

class CurrencyManagerStub implements  CurrencyManagerInterface
{
    const EUR = "EUR";
    const RUB = "RUB";

    /* @return string[] */
    public static function getAllCurrencies(): array
    {
        return [self::EUR, self::RUB];
    }

    public function getZeroForCurrency(
        string $curId
    ): AmountInCurrencyValInterface {
        Assert::inArray($curId, self::getAllCurrencies());
        return new AmountInCurrencyValStub($curId, 0);
    }

    public function convertAmountTo(
        AmountInCurrencyValInterface $amount,
        string $targetCurrency
    ): AmountInCurrencyValInterface {
        if($amount->getCurId() === self::RUB) {
            Assert::eq($targetCurrency, self::EUR);
            return new AmountInCurrencyValStub(
                self::EUR,
                $amount->getAmount() / 100
            );
        }
        if($amount->getCurId() === self::EUR) {
            Assert::eq($targetCurrency, self::RUB);
            return new AmountInCurrencyValStub(
                self::RUB,
                $amount->getAmount() * 100
            );
        }
        throw new \Error("invalid currency");
    }
}