# multicurtest-private-operations-service
Test task for Divan.ru: Service of account private operations


## Description
Service provice personal operations, that customer may act, end account effects if thats.


## Source structure
- exceptions
  - NotEnouthMoneyException
- managers
  - BankAccountManagerInterface
  - CurrencyManagerInterface
  - CurrencyOperationManagerInterface
  - CurrencySummaryManagerInterface
- records
  - BankAccountRecInterface
  - CurrencyOperationInAccountRequestRecInterface
  - CurrencySummaryInAccountRecInterface
- values
  - AmountInCurrencyValInterface
- PrivateOperationsService


## API
```php
<?php
namespace Pantagruel74\MulticurtestPrivateOperationsService;

final class PrivateOperationsService
{
    public function getConfirmedBalanceInCurrencyAccount(
        string $accountId,
        string $currency
    ): AmountInCurrencyValInterface {...}

    public function getConfirmedTotalBalanceInAccount(
        string $accountId
    ): AmountInCurrencyValInterface {...}

    public function replenishmentOfBalance(
        string $accountId,
        AmountInCurrencyValInterface $amount
    ): void {...}

    public function cashAmount(
        string $accountId,
        AmountInCurrencyValInterface $amount
    ): void {...}

    public function convertAmountToOtherCurrency(
        string $accountId,
        AmountInCurrencyValInterface $amount,
        string $targetCurrency
    ): void {...}
}
```