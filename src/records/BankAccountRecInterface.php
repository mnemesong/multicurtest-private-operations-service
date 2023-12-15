<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\records;

interface BankAccountRecInterface
{
    public function getId(): string;
    /* @return string[] */
    public function getCurrencies(): array;
    public function getMainCurId(): string;
}