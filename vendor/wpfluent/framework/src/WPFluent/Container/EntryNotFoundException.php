<?php

namespace NinjaTables\Framework\Container;

use Exception;
use NinjaTables\Framework\Container\Contracts\Psr\NotFoundExceptionInterface;

class EntryNotFoundException extends Exception implements NotFoundExceptionInterface
{
    //
}
