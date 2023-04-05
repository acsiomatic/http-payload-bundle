<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody;

/**
 * @internal
 */
final readonly class Outline
{
    public function __construct(
        /** @var string */
        public string $format,
        /** @var string */
        public string $contentType,
        /** @var array<string, mixed> */
        public array $serializationContext,
    ) {
    }
}
