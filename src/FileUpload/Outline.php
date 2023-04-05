<?php

namespace Acsiomatic\HttpPayloadBundle\FileUpload;

use Symfony\Component\Validator\Constraint;

/**
 * @internal
 */
final readonly class Outline
{
    public function __construct(
        /** @var array<Constraint> */
        public array $constraints,
    ) {
    }
}
