<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceStubs\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\managers\BankAccountManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\records\BankAccountRecInterface;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\records\BankAccountRecStub;
use Webmozart\Assert\Assert;

class BankAccountManagerStub implements BankAccountManagerInterface
{
    private array $accs = [];

    /**
     * @param BankAccountRecStub[] $accs
     */
    public function __construct(array $accs)
    {
        Assert::allIsAOf($accs, BankAccountRecStub::class);
        $this->accs = $accs;
    }


    public function getAccount(string $id): BankAccountRecInterface
    {
        $match = array_values(array_filter(
            $this->accs,
            fn(BankAccountRecInterface $acc) => ($acc->getId() === $id)
        ));
        Assert::notEmpty($match);
        return $match[0];
    }
}