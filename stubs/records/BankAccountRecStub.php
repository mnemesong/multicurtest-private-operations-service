<?php

namespace Pantagruel74\MulticurtestPrivateOperationsServiceStubs\records;

use Pantagruel74\MulticurtestPrivateOperationsService\records\BankAccountRecInterface;
use Webmozart\Assert\Assert;

/**
 * @property string[] $currencies
 */
class BankAccountRecStub implements BankAccountRecInterface
{
    private string $id;
    private array $currencies;
    private string $mainCurId;

    /**
     * @param string $id
     * @param array $currencies
     * @param string $mainCurId
     */
    public function __construct(string $id, array $currencies, string $mainCurId)
    {
        Assert::inArray($mainCurId, $currencies);
        $this->id = $id;
        $this->currencies = $currencies;
        $this->mainCurId = $mainCurId;
    }


    public function getId(): string
    {
        return $this->id;
    }

    /* @return string[] */
    public function getCurrencies(): array
    {
        return $this->currencies;
    }

    public function getMainCurId(): string
    {
        return $this->mainCurId;
    }
}