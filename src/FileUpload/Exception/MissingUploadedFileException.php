<?php

namespace Acsiomatic\HttpPayloadBundle\FileUpload\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class MissingUploadedFileException extends BadRequestHttpException
{
}
