<?php

namespace Acsiomatic\HttpPayloadBundle\RequestBody;

use Symfony\Component\Validator\Constraints\GroupSequence;

/**
 * @internal
 */
final class Preset
{
    public function __construct(
        public string $name,
        /** @var array<string> */
        public array $formats,
        /** @var array<string, mixed> */
        public array $deserializationContext,
        /** @var string|GroupSequence|array<string>|false|null */
        public string|GroupSequence|array|null|false $validationGroups,
    ) {
    }
}
