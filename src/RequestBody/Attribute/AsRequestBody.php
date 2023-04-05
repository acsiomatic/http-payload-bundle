<?php

namespace Acsiomatic\HttpPayloadBundle\RequestBody\Attribute;

use Symfony\Component\Validator\Constraints\GroupSequence;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final readonly class AsRequestBody
{
    /** @var array<string>|null */
    public array|null $formats;

    /**
     * @param string|array<string>|null $formats
     */
    public function __construct(
        public string|null $preset = null,
        string|array|null $formats = null,
        /** @var array<string, mixed>|null */
        public array|null $deserializationContext = null,
        /** @var string|GroupSequence|array<string>|false|null */
        public string|GroupSequence|array|null|false $validationGroups = null,
    ) {
        $this->formats = \is_string($formats) ? [$formats] : $formats;
    }
}
