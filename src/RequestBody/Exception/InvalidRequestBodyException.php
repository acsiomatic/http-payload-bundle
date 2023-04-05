<?php

namespace Acsiomatic\HttpPayloadBundle\RequestBody\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class InvalidRequestBodyException extends BadRequestHttpException
{
    public function __construct(string $message, ValidationFailedException $previous)
    {
        parent::__construct($message, $previous);
    }
}
