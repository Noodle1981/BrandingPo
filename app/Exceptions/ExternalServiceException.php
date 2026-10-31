<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Excepción lanzada cuando un servicio externo (API de red social, scraper, feed RSS)
 * experimenta un fallo de conexión, timeout o indisponibilidad temporal.
 */
class ExternalServiceException extends Exception
{
    protected ?string $servicio;

    public function __construct(string $message = 'El servicio externo no se encuentra disponible temporalmente.', ?string $servicio = null, int $code = 503, ?Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
        $this->servicio = $servicio;
    }

    public function getServicio(): ?string
    {
        return $this->servicio;
    }
}
