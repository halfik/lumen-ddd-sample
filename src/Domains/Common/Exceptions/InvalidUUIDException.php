<?php

namespace Domains\Common\Exceptions;

use InvalidArgumentException;

/**
 * Class InvalidUUIDException
 * @package App\Domain\Exceptions
 */
class InvalidUUIDException extends InvalidArgumentException
{
    /**
     * @param \Throwable|null $previous
     */
    public function __construct(\Throwable $previous = null)
    {
        parent::__construct('Invalid uuid', 0, $previous);
    }
}
