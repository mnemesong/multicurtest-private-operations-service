<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\managers;

use Pantagruel74\MulticurtestPrivateOperationsService\records\BankAccountRecInterface;

interface BankAccountManagerInterface
{
    public function getAccount(string $id): BankAccountRecInterface;
}