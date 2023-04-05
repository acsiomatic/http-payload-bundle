<?php

namespace Acsiomatic\HttpPayloadBundle\FileUpload\Attribute;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
final readonly class AsUploadedFile
{
    public function __construct(
        public string|null $preset = null,
        public string|null $name = null,
        /** @var Constraint|array<Constraint>|null */
        public Constraint|array|null $constraints = null,
    ) {
    }
}
