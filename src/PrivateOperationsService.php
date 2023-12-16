<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService;

use Pantagruel74\MulticurtestPrivateOperationsService\exceptions\NotEnouthMoneyException;
use Pantagruel74\MulticurtestPrivateOperationsService\managers\BankAccountManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\managers\CurrencyManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\managers\CurrencyOperationManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\managers\CurrencySummaryManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\records\CurrencyOperationInAccountRequestRecInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\records\CurrencySummaryInAccountRecInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;
use Webmozart\Assert\Assert;

final class PrivateOperationsService
{
    private BankAccountManagerInterface $bankAccountManager;
    private CurrencySummaryManagerInterface $currencySummaryManager;
    private CurrencyManagerInterface $currencyManager;
    private CurrencyOperationManagerInterface $currencyOperationManager;

    /**
     * @param BankAccountManagerInterface $bankAccountManager
     * @param CurrencySummaryManagerInterface $currencySummaryManager
     * @param CurrencyManagerInterface $currencyManager
     * @param CurrencyOperationManagerInterface $currencyOperationManager
     */
    public function __construct(
        BankAccountManagerInterface        $bankAccountManager,
        CurrencySummaryManagerInterface    $currencySummaryManager,
        CurrencyManagerInterface           $currencyManager,
        CurrencyOperationManagerInterface  $currencyOperationManager
    ) {
        $this->bankAccountManager = $bankAccountManager;
        $this->currencySummaryManager = $currencySummaryManager;
        $this->currencyManager = $currencyManager;
        $this->currencyOperationManager = $currencyOperationManager;
    }


    public function getConfirmedBalanceInCurrencyAccount(
        string $accountId,
        string $currency
    ): AmountInCurrencyValInterface {
        $account = $this->bankAccountManager->getAccount($accountId);
        $currencyExists = in_array($currency, $account->getCurrencies());
        Assert::true($currencyExists, "Currency " . $currency
            . " are not exists in account " . $accountId);
        return $this->calcConfirmedAmountInCurrency($accountId, $currency);
    }

    public function getFrozenAccountBalance(
        string $accountId,
        string $currency
    ): AmountInCurrencyValInterface {
        $account = $this->bankAccountManager->getAccount($accountId);
        $currencyExists = in_array($currency, $account->getCurrencies());
        Assert::false($currencyExists, "Currency " . $currency
            . " are not frozen in account " . $accountId);
        return $this->calcConfirmedAmountInCurrency($accountId, $currency);
    }

    public function getConfirmedTotalBalanceInAccount(
        string $accountId
    ): AmountInCurrencyValInterface {
        $account = $this->bankAccountManager->getAccount($accountId);
        $allCurrenciesInAcc = $account->getCurrencies();
        $allCurrenciesAmount = array_map(
            fn(string $cur) => $this
                ->calcConfirmedAmountInCurrency($accountId, $cur),
            $allCurrenciesInAcc
        );
        return array_reduce(
            $allCurrenciesAmount,
            fn(AmountInCurrencyValInterface $acc, AmountInCurrencyValInterface $el)
                => ($account->getMainCurId() === $el->getCurId())
                    ? $acc->plus($el)
                    : $acc->plus($this->currencyManager->convertAmountTo(
                        $el,
                        $account->getMainCurId()
                    )),
            $this->currencyManager->getZeroForCurrency($account->getMainCurId())
        );
    }

    public function replenishmentOfBalance(
        string $accountId,
        AmountInCurrencyValInterface $amount
    ): void {
        $account = $this->bankAccountManager->getAccount($accountId);
        Assert::inArray(
            $amount->getCurId(),
            $account->getCurrencies(),
            "Currency " . $amount->getCurId() . " are not created"
                . " in account " . $accountId
        );
        Assert::true(
            $amount->isPositive(),
            "Sum of replanishment can't be negative"
        );
        $replanishment = $this->currencyOperationManager
            ->createReplenishmentOperation($accountId, $amount);
        $this->currencyOperationManager
            ->saveNewOperations([$replanishment]);
        $this->currencyOperationManager
            ->confirmOperations([$replanishment->getId()]);
    }

    public function cashAmount(
        string $accountId,
        AmountInCurrencyValInterface $amount
    ): void {
        $account = $this->bankAccountManager->getAccount($accountId);
        Assert::inArray(
            $amount->getCurId(),
            $account->getCurrencies(),
            "Currency " . $amount->getCurId() . " are not created"
            . " in account " . $accountId
        );
        Assert::true(
            $amount->isPositive(),
            "Sum of cash can't be negative"
        );
        $cashOperation = $this->currencyOperationManager
            ->createCashOperationInProcessing($accountId, $amount);
        $this->currencyOperationManager
            ->saveNewOperations([$cashOperation]);
        try {
            $this->assertPessimisticBalanceIsPositive(
                $accountId,
                $amount->getCurId()
            );
            sleep(1);
            $this->assertPessimisticBalanceIsPositive(
                $accountId,
                $amount->getCurId()
            );
            $this->currencyOperationManager
                ->confirmOperations([$cashOperation->getId()]);
        } catch (NotEnouthMoneyException $e) {
            $this->currencyOperationManager
                ->declineOperations([$cashOperation->getId()]);
            throw $e;
        }
    }

    public function convertAmountToOtherCurrency(
        string $accountId,
        AmountInCurrencyValInterface $amount,
        string $targetCurrency
    ): void {
        $account = $this->bankAccountManager->getAccount($accountId);
        Assert::inArray(
            $amount->getCurId(),
            $account->getCurrencies(),
            "Currency " . $amount->getCurId() . " are not created"
            . " in account " . $accountId
        );
        Assert::true(
            $amount->isPositive(),
            "Sum to convert can't be negative"
        );
        $writeOffOperation = $this->currencyOperationManager
            ->createWriteOffCaseConversion($accountId, $amount);
        $this->currencyOperationManager
            ->saveNewOperations([$writeOffOperation]);
        try {
            $this->assertPessimisticBalanceIsPositive(
                $accountId,
                $amount->getCurId()
            );
            sleep(1);
            $this->assertPessimisticBalanceIsPositive(
                $accountId,
                $amount->getCurId()
            );
            $targetAmount = $this->currencyManager
                ->convertAmountTo($amount, $targetCurrency);
            $writeInOperation = $this->currencyOperationManager
                ->createWriteInCaseConversion($accountId, $targetAmount);
            $this->currencyOperationManager
                ->saveNewOperations([$writeInOperation]);
            $this->currencyOperationManager
                ->confirmOperations([
                    $writeOffOperation->getId(),
                    $writeInOperation->getId()
                ]);
        } catch (NotEnouthMoneyException $e) {
            $this->currencyOperationManager
                ->declineOperations([$writeOffOperation->getId()]);
            throw $e;
        }
    }

    private function assertPessimisticBalanceIsPositive(
        string $accountId,
        string $curId
    ): void {
        $pessimisticAmount = $this->calcPessimisticAmountInCurrency(
            $accountId,
            $curId
        );
        if (!$pessimisticAmount->isPositive() && !$pessimisticAmount->isZero()) {
            throw new NotEnouthMoneyException();
        };
    }

    private function getAllUndeclinedOperationsAfter(
        string $accountId,
        string $curId,
        ?int $timestamp
    ): array {
        $operationsAfterSummary = $this->currencyOperationManager
            ->getAllOperationsAfter($accountId, $curId, $timestamp);
        return array_filter(
            $operationsAfterSummary,
            fn(CurrencyOperationInAccountRequestRecInterface $op)
                => ($op->isDeclined() === false)
        );
    }

    /* @param CurrencyOperationInAccountRequestRecInterface[] $operations */
    private function calcOperationsSum(
        array $operations,
        string $curId
    ): AmountInCurrencyValInterface {
        Assert::allSubclassOf($operations,
            CurrencyOperationInAccountRequestRecInterface::class);
        return array_reduce(
            $operations,
            fn(
                AmountInCurrencyValInterface $acc,
                CurrencyOperationInAccountRequestRecInterface $el
            ) => $acc->plus($el->getAmount()),
            $this->currencyManager->getZeroForCurrency($curId)
        );
    }

    private function calcConfirmedAmountInCurrency(
        string $accountId,
        string $currency
    ): AmountInCurrencyValInterface {
        $lastSummary = $this->currencySummaryManager
            ->getLastSummaryForAccount($accountId, $currency);
        $lastSummaryAmount = is_null($lastSummary)
            ? $this->currencyManager->getZeroForCurrency($currency)
            : $lastSummary->getAmount();
        $lastSummaryTimestamp = is_null($lastSummary)
            ? null
            : $lastSummary->getTimestamp();
        $operationsAfterSummary = $this->getAllUndeclinedOperationsAfter(
            $accountId,
            $currency,
            $lastSummaryTimestamp
        );
        $operationsAfterSummary = array_filter(
            $operationsAfterSummary,
            fn(CurrencyOperationInAccountRequestRecInterface $op)
                => ($op->isConfirmed() === true)
        );
        return $lastSummaryAmount->plus(
            $this->calcOperationsSum($operationsAfterSummary, $currency)
        );
    }

    private function calcPessimisticAmountInCurrency(
        string $accountId,
        string $currency
    ): AmountInCurrencyValInterface {
        $lastSummary = $this->currencySummaryManager
            ->getLastSummaryForAccount($accountId, $currency);
        $lastSummaryAmount = is_null($lastSummary)
            ? $this->currencyManager->getZeroForCurrency($currency)
            : $lastSummary->getAmount();
        $lastSummaryTimestamp = is_null($lastSummary)
            ? null
            : $lastSummary->getTimestamp();
        $operationsAfterSummary = $this->getAllUndeclinedOperationsAfter(
            $accountId,
            $currency,
            $lastSummaryTimestamp
        );
        $operationsAfterSummary = array_filter(
            $operationsAfterSummary,
            fn(CurrencyOperationInAccountRequestRecInterface $op)
                => (($op->isDeclined() === false)
                    && !($op->getAmount()->isPositive() && !$op->isConfirmed()))
        );
        return $lastSummaryAmount->plus(
            $this->calcOperationsSum($operationsAfterSummary, $currency)
        );
    }

}