<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceTest;

use Pantagruel74\MulticurtestPrivateOperationsService\exceptions\NotEnouthMoneyException;
use Pantagruel74\MulticurtestPrivateOperationsService\PrivateOperationsService;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\managers\BankAccountManagerStub;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\managers\CurrencyManagerStub;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\managers\CurrencyOperationManagerStub;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\managers\CurrencySummaryManagerStub;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\records\CurrencyOperationInAccountRequestRecStub;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\records\CurrencySummaryInAccountRecStub;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\values\AmountInCurrencyValStub;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class PrivateOperationsServiceTest extends TestCase
{
    const SUMMARY_RUB_ID = "a379af14-0fa2-4ba7-a8c3-97f186dc969f";
    const SUMMARY_EUR_ID = "d6d20080-d80d-4c0a-bfda-e79ac95a0351";
    const OPERATION_RUB_1_ID = "967ca2a9-f02d-4fc9-bb2e-3378523fda4f";
    const OPERATION_RUB_2_ID = "f1512d51-2462-4cc9-a22f-b63b9f5a3e3a";
    const OPERATION_EUR_1_ID = "e7eb8bfa-fc6d-4d0b-8827-2e45d180484c";

    public function testGetConfirmedBalanceInCurrencyWithSummaryValid()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_EUR_ID,
                "EUR",
                new AmountInCurrencyValStub("EUR", 200),
                $initTimestamp
            ),
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_RUB_ID,
                "RUB",
                new AmountInCurrencyValStub("RUB", 2000),
                $initTimestamp
            ),
        ]);
        $operationManager = new CurrencyOperationManagerStub([
            new CurrencyOperationInAccountRequestRecStub(
                self::OPERATION_RUB_1_ID,
                new AmountInCurrencyValStub("RUB", 1000),
                true,
                false,
                $initTimestamp + 1000,
            ),
            //Declined
            new CurrencyOperationInAccountRequestRecStub(
                self::OPERATION_RUB_2_ID,
                new AmountInCurrencyValStub("RUB", -1500),
                false,
                true,
                $initTimestamp + 1000,
            ),
            //Out of last summary range
            new CurrencyOperationInAccountRequestRecStub(
                self::OPERATION_EUR_1_ID,
                new AmountInCurrencyValStub("EUR", -1500),
                false,
                true,
                $initTimestamp - 1000,
            ),
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $result1 = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "RUB"
        );
        $this->assertEquals(3000, $result1->getAmount());
        $this->assertEquals("RUB", $result1->getCurId());
        $result2 = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "EUR"
        );
        $this->assertEquals(200, $result2->getAmount());
        $this->assertEquals("EUR", $result2->getCurId());
    }

    public function testGetConfirmedBalanceInCurrencyWithoutSummaryValid()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $result1 = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "RUB"
        );
        $this->assertEquals(0, $result1->getAmount());
        $result2 = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "EUR"
        );
        $this->assertEquals(0, $result2->getAmount());
    }

    public function testGetConfirmedBalanceInCurrencyInvalidAccId()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(\InvalidArgumentException::class);
        $result1 = $service->getConfirmedBalanceInCurrencyAccount(
            "sjad98ja9o21",
            "RUB"
        );
    }

    public function testGetConfirmedBalanceInCurrencyInvalidCurrency()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(\InvalidArgumentException::class);
        $result1 = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "ASJl"
        );
    }

    public function testGetConfirmedTotalBalanceValid()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_EUR_ID,
                "EUR",
                new AmountInCurrencyValStub("EUR", 200),
                $initTimestamp
            ),
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_RUB_ID,
                "RUB",
                new AmountInCurrencyValStub("RUB", 2000),
                $initTimestamp
            ),
        ]);
        $operationManager = new CurrencyOperationManagerStub([
            new CurrencyOperationInAccountRequestRecStub(
                self::OPERATION_RUB_1_ID,
                new AmountInCurrencyValStub("RUB", 1000),
                true,
                false,
                $initTimestamp + 1000,
            ),
            //Declined
            new CurrencyOperationInAccountRequestRecStub(
                self::OPERATION_RUB_2_ID,
                new AmountInCurrencyValStub("RUB", -1500),
                false,
                true,
                $initTimestamp + 1000,
            ),
            //Out of last summary range
            new CurrencyOperationInAccountRequestRecStub(
                self::OPERATION_EUR_1_ID,
                new AmountInCurrencyValStub("EUR", -1500),
                false,
                true,
                $initTimestamp - 1000,
            ),
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $result = $service->getConfirmedTotalBalanceInAccount(
            BankAccountManagerStub::ACC_ID
        );
        $this->expectOutputString("");
        $this->assertEquals(23000, $result->getAmount());
    }

    public function testGetConfirmedTotalBalanceInvalildAccId()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(\InvalidArgumentException::class);
        $result = $service->getConfirmedTotalBalanceInAccount(
            "dasjud876"
        );
    }

    public function testReplenishmentBalanceValid()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_EUR_ID,
                "EUR",
                new AmountInCurrencyValStub("EUR", 200),
                $initTimestamp
            ),
        ]);
        $operationManager = new CurrencyOperationManagerStub([
            new CurrencyOperationInAccountRequestRecStub(
                self::OPERATION_EUR_1_ID,
                new AmountInCurrencyValStub("EUR", 1500),
                true,
                false,
                $initTimestamp + 1000,
            ),
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $service->replenishmentOfBalance(
            BankAccountManagerStub::ACC_ID,
            new AmountInCurrencyValStub("EUR", 300),
        );
        $result = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "EUR"
        );
        $this->assertEquals(2000, $result->getAmount());
    }

    public function testReplenishmentBalanceInvalidAccId()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(\InvalidArgumentException::class);
        $service->replenishmentOfBalance(
            "dauns8",
            new AmountInCurrencyValStub("EUR", 300),
        );
    }

    public function testReplenishmentBalanceInvalidAmount()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(\InvalidArgumentException::class);
        $service->replenishmentOfBalance(
            BankAccountManagerStub::ACC_ID,
            new AmountInCurrencyValStub("EUR", -300),
        );
    }

    public function testReplenishmentBalanceInvalidCurrency()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(\InvalidArgumentException::class);
        $service->replenishmentOfBalance(
            BankAccountManagerStub::ACC_ID,
            new AmountInCurrencyValStub("dasn", 300),
        );
    }

    public function testCashAmountValid()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_EUR_ID,
                "EUR",
                new AmountInCurrencyValStub("EUR", 200),
                $initTimestamp
            ),
        ]);
        $operationManager = new CurrencyOperationManagerStub([
            new CurrencyOperationInAccountRequestRecStub(
                self::OPERATION_EUR_1_ID,
                new AmountInCurrencyValStub("EUR", 1500),
                true,
                false,
                $initTimestamp + 1000,
            ),
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $service->cashAmount(
            BankAccountManagerStub::ACC_ID,
            new AmountInCurrencyValStub("EUR", 300),
        );
        $result = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "EUR"
        );
        $this->expectOutputString("");
        $this->assertEquals(1400, $result->getAmount());
    }

    public function testCashAmountDeclined()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_EUR_ID,
                "EUR",
                new AmountInCurrencyValStub("EUR", 200),
                $initTimestamp
            ),
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(NotEnouthMoneyException::class);
        $service->cashAmount(
            BankAccountManagerStub::ACC_ID,
            new AmountInCurrencyValStub("EUR", 300),
        );
        $result = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "EUR"
        );
        $this->assertEquals(200, $result->getAmount());
    }

    public function testCashAmountInvalidAccId()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(\InvalidArgumentException::class);
        $service->cashAmount(
            "dauns8",
            new AmountInCurrencyValStub("EUR", 300),
        );
    }

    public function testCashAmountInvalidAmount()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(\InvalidArgumentException::class);
        $service->cashAmount(
            BankAccountManagerStub::ACC_ID,
            new AmountInCurrencyValStub("EUR", -300),
        );
    }

    public function testCashAmountInvalidCurrency()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(\InvalidArgumentException::class);
        $service->replenishmentOfBalance(
            BankAccountManagerStub::ACC_ID,
            new AmountInCurrencyValStub("dasn", 300),
        );
    }

    public function testAmountConversionValid()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_EUR_ID,
                "EUR",
                new AmountInCurrencyValStub("EUR", 200),
                $initTimestamp
            ),
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_RUB_ID,
                "RUB",
                new AmountInCurrencyValStub("RUB", 2000),
                $initTimestamp
            ),
        ]);
        $operationManager = new CurrencyOperationManagerStub([
            new CurrencyOperationInAccountRequestRecStub(
                self::OPERATION_EUR_1_ID,
                new AmountInCurrencyValStub("EUR", 300),
                true,
                false,
                $initTimestamp + 1000,
            ),
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $service->convertAmountToOtherCurrency(
            BankAccountManagerStub::ACC_ID,
            new AmountInCurrencyValStub("EUR", 500),
            "RUB"
        );
        $resultEur = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "EUR"
        );
        $this->assertEquals(0, $resultEur->getAmount());
        $resultRub = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "RUB"
        );
        $this->assertEquals(52000, $resultRub->getAmount());
    }

    public function testAmountConversionDeclined()
    {
        $initTimestamp = (new \DateTime())->getTimestamp();
        $accManager = new BankAccountManagerStub();
        $curManager = new CurrencyManagerStub();
        $summmaryManager = new CurrencySummaryManagerStub([
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_EUR_ID,
                "EUR",
                new AmountInCurrencyValStub("EUR", 200),
                $initTimestamp
            ),
            new CurrencySummaryInAccountRecStub(
                self::SUMMARY_RUB_ID,
                "RUB",
                new AmountInCurrencyValStub("RUB", 2000),
                $initTimestamp
            ),
        ]);
        $operationManager = new CurrencyOperationManagerStub([
        ]);
        $service = new PrivateOperationsService(
            $accManager,
            $summmaryManager,
            $curManager,
            $operationManager
        );
        $this->expectException(NotEnouthMoneyException::class);
        $service->convertAmountToOtherCurrency(
            BankAccountManagerStub::ACC_ID,
            new AmountInCurrencyValStub("EUR", 500),
            "RUB"
        );
        $this->expectException(NotEnouthMoneyException::class);
        $resultEur = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "EUR"
        );
        $this->assertEquals(200, $resultEur->getAmount());
        $resultRub = $service->getConfirmedBalanceInCurrencyAccount(
            BankAccountManagerStub::ACC_ID,
            "RUB"
        );
        $this->assertEquals(200, $resultRub->getAmount());
    }
}