<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\records\CurrencyOperationInAccountRequestRecInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;

interface CurrencyOperationManagerInterface
{
    /* @return CurrencyOperationInAccountRequestRecInterface[] */
    public function getAllOperationsAfter(
        string $accId,
        string $curId,
        ?int $afterTimestamp
    ): array;

    public function createReplenishmentOperation(
        string $accId,
        AmountInCurrencyValInterface $amount
    ): CurrencyOperationInAccountRequestRecInterface;

    public function createCashOperationInProcessing(
        string $accId,
        AmountInCurrencyValInterface $amount
    ): CurrencyOperationInAccountRequestRecInterface;

    public function saveNewOperations(
        array $operations
    ): void;

    public function confirmOperations(
        array $operationIds
    ): void;

    public function declineOperations(
        array $operationIds
    ): void;

}