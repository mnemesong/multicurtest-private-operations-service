<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceStubs\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\managers\CurrencySummaryManagerInterface;
use Pantagruel74\MulticurtestPrivateOperationsService\records\CurrencySummaryInAccountRecInterface;
use Pantagruel74\MulticurtestPrivateOperationsServiceStubs\records\CurrencySummaryInAccountRecStub;
use Webmozart\Assert\Assert;

class CurrencySummaryManagerStub implements CurrencySummaryManagerInterface
{
    private array $summaries = [];

    /**
     * @param CurrencySummaryInAccountRecStub[] $currencySummaryInAccountRec
     */
    public function __construct(array $currencySummaryInAccountRec)
    {
        Assert::allIsAOf(
            $currencySummaryInAccountRec,
            CurrencySummaryInAccountRecStub::class
        );
        $this->summaries = $currencySummaryInAccountRec;
    }


    public function getLastSummaryForAccount(
        string $accId,
        string $curId
    ): ?CurrencySummaryInAccountRecInterface {
        $match = array_values(array_filter(
            $this->summaries,
            fn(CurrencySummaryInAccountRecInterface $s)
                => ($s->getCurId() === $curId)
        ));
        return (count($match) > 0) ? $match[0] : null;
    }
}