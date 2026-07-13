<?php

namespace App\Exception;

use InvalidArgumentException;

/**
 * Exception levée lorsqu'une chambre n'est pas disponible pour une période donnée.
 *
 * Cette exception étend InvalidArgumentException et permet d'inclure
 * une clé de traduction et des paramètres pour un message d'erreur internationalisé.
 */
class RoomUnavailableException extends InvalidArgumentException
{
    /**
     * Clé de traduction pour le message d'erreur.
     *
     * @var string|null
     */
    private ?string $translationKey;

    /**
     * Paramètres pour la traduction du message d'erreur.
     *
     * @var array
     */
    private array $translationParameters;

    /**
     * Constructeur de l'exception RoomUnavailableException.
     *
     * @param string $message Le message d'erreur par défaut.
     * @param string|null $translationKey La clé de traduction optionnelle.
     * @param array $translationParameters Les paramètres de traduction optionnels.
     * @param int $code Le code d'erreur.
     * @param \Throwable|null $previous L'exception précédente dans la chaîne.
     */
    public function __construct(
        string $message = "La chambre n'est pas disponible pour la période sélectionnée.",
        ?string $translationKey = null,
        array $translationParameters = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->translationKey = $translationKey;
        $this->translationParameters = $translationParameters;
    }

    /**
     * Retourne la clé de traduction associée à cette exception.
     *
     * @return string|null La clé de traduction.
     */
    public function getTranslationKey(): ?string
    {
        return $this->translationKey;
    }

    /**
     * Retourne les paramètres de traduction associés à cette exception.
     *
     * @return array Les paramètres de traduction.
     */
    public function getTranslationParameters(): array
    {
        return $this->translationParameters;
    }
}
