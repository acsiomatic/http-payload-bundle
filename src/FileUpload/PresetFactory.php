<?php

namespace Acsiomatic\HttpPayloadBundle\FileUpload;

use Symfony\Component\Validator\Constraint;

/**
 * @internal
 */
final class PresetFactory
{
    /**
     * @template T of Constraint
     *
     * @param array<class-string<T>, array<string, mixed>> $constraints
     */
    public function __invoke(string $name, array $constraints): Preset
    {
        $objects = [];
        foreach ($constraints as $class => $arguments) {
            $objects[] = new $class(...$arguments);
        }

        return new Preset($name, $objects);
    }
}
