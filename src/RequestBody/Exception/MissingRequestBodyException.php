<?php

namespace Acsiomatic\HttpPayloadBundle\RequestBody\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class MissingRequestBodyException extends BadRequestHttpException
{
}
