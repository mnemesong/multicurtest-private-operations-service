<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\records\CurrencySummaryInAccountRecInterface;

interface CurrencySummaryManagerInterface
{
    public function getLastSummaryForAccount(
        string $accId,
        string $curId
    ): ?CurrencySummaryInAccountRecInterface;
}