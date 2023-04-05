<?php

namespace Acsiomatic\HttpPayloadBundle\FileUpload;

use Symfony\Component\Validator\Constraint;

/**
 * @internal
 */
final class Preset
{
    public function __construct(
        public string $name,
        /** @var array<Constraint> */
        public array $constraints,
    ) {
    }
}
