<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody\Exception;

use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

final class ContentTypeMismatchException extends NotAcceptableHttpException
{
}
