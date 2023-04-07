<?php

namespace Acsiomatic\HttpPayloadBundle\FileUpload;

use Acsiomatic\HttpPayloadBundle\FileUpload\Attribute\MapUploadedFile;
use Acsiomatic\HttpPayloadBundle\FileUpload\Exception\PresetNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\Validator\Constraint;

/**
 * @internal
 */
final readonly class OutlineResolver
{
    private const PRESET_DEFAULT = 'default';

    public function __construct(
        /** @var iterable<Preset> */
        #[TaggedIterator('acsiomatic.http_payload.file_upload.preset')] private iterable $presets,
    ) {
    }

    public function resolve(MapUploadedFile $attribute): Outline
    {
        $preset = $this->getPreset($attribute->preset ?? self::PRESET_DEFAULT);

        $constraints = $attribute->constraints ?? $preset->constraints;
        if ($constraints instanceof Constraint) {
            $constraints = [$constraints];
        }

        return new Outline($constraints);
    }

    private function getPreset(string $name): Preset
    {
        $available = [];
        foreach ($this->presets as $preset) {
            $available[] = $preset->name;
            if ($preset->name === $name) {
                return $preset;
            }
        }

        throw new PresetNotFoundException(sprintf('File upload preset "%s" not found, available: %s', $name, implode(', ', $available)));
    }
}
