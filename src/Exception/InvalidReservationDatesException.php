<?php

namespace App\Exception;

use InvalidArgumentException;

class InvalidReservationDatesException extends InvalidArgumentException
{
    private ?string $translationKey;
    private array $translationParameters;

    public function __construct(
        string $message = "Les dates de réservation sont invalides.",
        ?string $translationKey = null,
        array $translationParameters = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->translationKey = $translationKey;
        $this->translationParameters = $translationParameters;
    }

    public function getTranslationKey(): ?string
    {
        return $this->translationKey;
    }

    public function getTranslationParameters(): array
    {
        return $this->translationParameters;
    }
}
