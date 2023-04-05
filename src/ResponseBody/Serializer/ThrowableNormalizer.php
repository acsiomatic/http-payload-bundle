<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * @internal
 */
final class ThrowableNormalizer implements NormalizerInterface
{
    /**
     * @return array<mixed>
     */
    public function normalize(mixed $object, string $format = null, array $context = []): array
    {
        \assert($object instanceof \Throwable);

        return [
            'error' => $object->getMessage(),
        ];
    }

    public function supportsNormalization(mixed $data, string $format = null): bool
    {
        return $data instanceof \Throwable;
    }
}
