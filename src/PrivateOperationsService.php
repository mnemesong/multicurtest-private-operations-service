<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService;

use Pantagruel74\MulticurtestPrivateOperationsService\managers\BankAccountManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\managers\CurrencyManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\managers\CurrencyOperationManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\managers\CurrencySummaryManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\records\CurrencyOperationInAccountRequestRecInterface;
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
        return $this->calcAmountInCurrency($accountId, $currency);
    }

    public function getConfirmedTotalBalanceInAccount(
        string $accountId
    ): AmountInCurrencyValInterface {
        $account = $this->bankAccountManager->getAccount($accountId);
        $allCurrenciesInAcc = $account->getCurrencies();
        $allCurrenciesAmount = array_map(
            fn(string $cur) => $this->calcAmountInCurrency($accountId, $cur),
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
        $currencyDirtyBalance = $this->calcAmountInCurrency(
            $accountId,
            $amount->getCurId(),
            false
        );
        if($currencyDirtyBalance->minus($amount)->isPositive()) {
            sleep(1);
            $this->currencyOperationManager
                ->confirmOperations([$cashOperation->getId()]);
        } else {
            $this->currencyOperationManager
                ->declineOperations([$cashOperation->getId()]);
        }
    }

    private function calcAmountInCurrency(
        string $accountId,
        string $currency,
        bool $onlyConfirmed = true
    ): AmountInCurrencyValInterface {
        $lastSummary = $this->currencySummaryManager
            ->getLastSummaryForAccount($accountId, $currency);
        $lastSummaryAmount = is_null($lastSummary)
            ? $this->currencyManager->getZeroForCurrency($currency)
            : $lastSummary->getAmount();
        $lastSummaryTimestamp = is_null($lastSummary)
            ? null
            : $lastSummary->getTimestamp();
        $operationsAfterSummary = $this->currencyOperationManager
            ->getAllOperationsAfter($accountId, $currency, $lastSummaryTimestamp);
        $operationsAfterSummary = array_filter(
            $operationsAfterSummary,
            fn(CurrencyOperationInAccountRequestRecInterface $op)
                => ($op->isDeclined() === false)
        );
        if($onlyConfirmed === true) {
            $operationsAfterSummary = array_filter(
                $operationsAfterSummary,
                fn(CurrencyOperationInAccountRequestRecInterface $op)
                    => ($op->isConfirmed() === true)
            );
        }
        return array_reduce(
            $operationsAfterSummary,
            fn(
                AmountInCurrencyValInterface $acc,
                CurrencyOperationInAccountRequestRecInterface $el
            ) => $acc->plus($el->getAmount()),
            $lastSummaryAmount
        );
    }

}