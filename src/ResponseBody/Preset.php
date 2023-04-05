<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody;

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
        public array $serializationContext,
    ) {
    }
}
