<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceStubs\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\managers\CurrencyOperationManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\records\CurrencyOperationInAccountRequestRecInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\values\AmountInCurrencyValInterface;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\records\CurrencyOperationInAccountRequestRecStub;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\values\AmountInCurrencyValStub;
use Ramsey\Uuid\Uuid;
use Webmozart\Assert\Assert;

class CurrencyOperationManagerStub implements CurrencyOperationManagerInterface
{
    private array $operations = [];

    /**
     * @param CurrencyOperationInAccountRequestRecStub[] $operations
     */
    public function __construct(array $operations)
    {
        Assert::allIsAOf(
            $operations,
            CurrencyOperationInAccountRequestRecStub::class
        );
        $this->operations = $operations;
    }

    public function getAllOperationsAfter(
        string $accId,
        string $curId,
        ?int $afterTimestamp
    ): array {
        $matchOperations = array_filter(
            $this->operations,
            fn(CurrencyOperationInAccountRequestRecStub $op)
                => ($op->getAmount()->getCurId() === $curId)
        );
        if(!is_null($afterTimestamp)) {
            $matchOperations = array_filter(
                $matchOperations,
                fn(CurrencyOperationInAccountRequestRecStub $op)
                => ($op->getTimestamp() >= $afterTimestamp)
            );
        }
        return $matchOperations;
    }

    public function createReplenishmentOperation(
        string $accId,
        AmountInCurrencyValInterface $amount
    ): CurrencyOperationInAccountRequestRecInterface {
        Assert::isAOf($amount, AmountInCurrencyValStub::class);
        /* @var AmountInCurrencyValStub $amount */
        Assert::true($amount->getAmount() > 0);
        return new CurrencyOperationInAccountRequestRecStub(
            Uuid::uuid4(),
            $amount,
            false,
            false,
            (new \DateTime("now"))->getTimestamp()
        );
    }

    public function createCashOperationInProcessing(
        string $accId,
        AmountInCurrencyValInterface $amount
    ): CurrencyOperationInAccountRequestRecInterface {
        Assert::isAOf($amount, AmountInCurrencyValStub::class);
        /* @var AmountInCurrencyValStub $amount */
        Assert::true($amount->getAmount() > 0);
        return new CurrencyOperationInAccountRequestRecStub(
            Uuid::uuid4(),
            $amount->reverse(),
            false,
            false,
            (new \DateTime("now"))->getTimestamp()
        );
    }

    public function saveNewOperations(array $operations): void
    {
        Assert::allIsAOf(
            $operations,
            CurrencyOperationInAccountRequestRecStub::class
        );
        /* @var CurrencyOperationInAccountRequestRecStub[] $operations */
        $thisOperationsIds = array_map(
            fn(CurrencyOperationInAccountRequestRecStub $op) => $op->getId(),
            $this->operations
        );
        foreach ($operations as $op) {
            Assert::false(in_array($op->getId(), $thisOperationsIds));
            $this->operations[] = $op;
        }
    }

    public function confirmOperations(array $operationIds): void
    {
        $this->operations = array_map(
            fn(CurrencyOperationInAccountRequestRecStub $op)
                => (in_array($op->getId(), $operationIds))
                    ? $op->asConfirmed()
                    : $op,
            $this->operations
        );
    }

    public function declineOperations(array $operationIds): void
    {
        $this->operations = array_map(
            fn(CurrencyOperationInAccountRequestRecStub $op)
                => (in_array($op->getId(), $operationIds))
                    ? $op->asDeclined()
                    : $op,
            $this->operations
        );
    }
}