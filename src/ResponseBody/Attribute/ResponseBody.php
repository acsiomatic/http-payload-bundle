<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
final readonly class ResponseBody
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
        public array|null $serializationContext = null,
    ) {
        $this->formats = \is_string($formats) ? [$formats] : $formats;
    }
}
