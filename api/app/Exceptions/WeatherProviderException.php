<?php

namespace App\Exceptions;

use Exception;

class WeatherProviderException extends Exception
{
    protected array $context;

    public function __construct(
        string $message = "",
        int $code = 0 ,
        array $context = [],
        \Throwable $previous = null
    )
    {
        parent::__construct($message);
        $this->context = $context;
        $this->code = $code;
    }

    public function getContext(): array
    {
        return $this->context;
    }
}
