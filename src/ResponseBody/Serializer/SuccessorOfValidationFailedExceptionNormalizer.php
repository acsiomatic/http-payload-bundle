<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

/**
 * @internal
 */
final class SuccessorOfValidationFailedExceptionNormalizer implements NormalizerInterface
{
    /**
     * @return array<mixed>
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        \assert($object instanceof \Throwable && $object->getPrevious() instanceof ValidationFailedException);

        $errors = [];
        foreach ($object->getPrevious()->getViolations() as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        return [
            'error' => $object->getMessage(),
            'constraint_violations' => $errors,
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof \Throwable && $data->getPrevious() instanceof ValidationFailedException;
    }
}
