<?php

namespace Acsiomatic\HttpPayloadBundle\RequestBody;

use Symfony\Component\Validator\Constraints\GroupSequence;

/**
 * @internal
 */
final readonly class Outline
{
    public function __construct(
        /** @var string */
        public string $format,
        public string $contentType,
        /** @var array<string, mixed> */
        public array $deserializationContext,
        /** @var string|GroupSequence|array<string>|false|null */
        public string|GroupSequence|array|false|null $validationGroups,
    ) {
    }
}
