<?php

namespace App\Exception;

use InvalidArgumentException;

class InvalidReservationDatesException extends InvalidArgumentException
{
    public function __construct(string $message = "Les dates de réservation sont invalides.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
