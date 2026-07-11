<?php

namespace App\Exception;

use InvalidArgumentException;

class RoomUnavailableException extends InvalidArgumentException
{
    public function __construct(string $message = "La chambre n'est pas disponible pour la période sélectionnée.", int $code = 0, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
