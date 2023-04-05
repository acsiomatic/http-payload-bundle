<?php

namespace Acsiomatic\HttpPayloadBundle\RequestBody;

use Acsiomatic\HttpPayloadBundle\RequestBody\Attribute\AsRequestBody;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\ContentTypeMismatchException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\PresetNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * @internal
 */
final readonly class OutlineResolver
{
    private const PRESET_DEFAULT = 'default';

    public function __construct(
        private MimeTypesInterface $mimeTypes,
        /** @var iterable<Preset> */
        #[TaggedIterator('acsiomatic.http_payload.request_body.preset')] private iterable $presets,
    ) {
    }

    public function resolve(Request $request, AsRequestBody $attribute): Outline
    {
        $preset = $this->getPreset($attribute->preset ?? self::PRESET_DEFAULT);

        $knownFormats = $attribute->formats ?? $preset->formats;
        $deserializationContext = $attribute->deserializationContext ?? $preset->deserializationContext;
        $validationGroups = $attribute->validationGroups ?? $preset->validationGroups;

        $givenContentType = (string) $request->headers->get('content-type');
        $givenFormats = $this->mimeTypes->getExtensions($givenContentType);
        $matchedFormat = array_values(array_intersect($knownFormats, $givenFormats))[0] ?? null;
        if ($matchedFormat === null) {
            throw new ContentTypeMismatchException(sprintf('Cannot match "%s" mime type with "%s" formats', $givenContentType, implode(', ', $knownFormats)));
        }

        return new Outline(
            $matchedFormat,
            $givenContentType,
            $deserializationContext,
            $validationGroups,
        );
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

        throw new PresetNotFoundException(sprintf('Request body preset "%s" not found, available: %s', $name, implode(', ', $available)));
    }
}
