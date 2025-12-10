<?php

namespace NinjaTables\Framework\Container\Contracts;

use Exception;
use NinjaTables\Framework\Container\Contracts\Psr\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
