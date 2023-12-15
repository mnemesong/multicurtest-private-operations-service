<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceStubs\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\managers\BankAccountManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\records\BankAccountRecInterface;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\records\BankAccountRecStub;
use Webmozart\Assert\Assert;

class BankAccountManagerStub implements BankAccountManagerInterface
{
    const ACC_ID = "d50e2997-a9be-4d9b-9894-e97ce1b3e24e";

    public function getAccount(string $id): BankAccountRecInterface
    {
        Assert::eq($id, self::ACC_ID);
        return new BankAccountRecStub(
            self::ACC_ID,
            CurrencyManagerStub::getAllCurrencies(),
            CurrencyManagerStub::RUB
        );
    }
}