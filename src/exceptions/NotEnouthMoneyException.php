<?php

namespace Pantagruel74\MulticurtestPrivateOperationsService\exceptions;

class NotEnouthMoneyException extends \DomainException
{
    protected $message = "Not enouth money for this operation";
}