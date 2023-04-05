<?php

namespace Acsiomatic\HttpPayloadBundle\ResponseBody;

use Acsiomatic\HttpPayloadBundle\ResponseBody\Attribute\ResponseBody;
use Acsiomatic\HttpPayloadBundle\ResponseBody\Exception\ContentTypeMismatchException;
use Acsiomatic\HttpPayloadBundle\ResponseBody\Exception\PresetNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\MimeTypesInterface;

/**
 * @internal
 */
final readonly class OutlineResolver
{
    private const PRESET_DEFAULT = 'default';

    private const WILDCARD = '*/*';

    public function __construct(
        private MimeTypesInterface $mimeTypes,
        /** @var iterable<Preset> */
        #[TaggedIterator('acsiomatic.http_payload.response_body.preset')] private iterable $presets,
    ) {
    }

    public function resolve(Request $request, ResponseBody $attribute): Outline
    {
        $preset = $this->getPreset($attribute->preset ?? self::PRESET_DEFAULT);

        $knownFormats = $attribute->formats ?? $preset->formats;
        $serializationContext = $attribute->serializationContext ?? $preset->serializationContext;

        $acceptableContentTypes = $request->getAcceptableContentTypes();
        if ($acceptableContentTypes === []) {
            $acceptableContentTypes = [self::WILDCARD];
        }

        foreach ($acceptableContentTypes as $acceptableContentType) {
            foreach ($knownFormats as $format) {
                $contentTypesByFormat = array_filter($this->mimeTypes->getMimeTypes($format));
                if ($contentTypesByFormat === []) {
                    continue;
                }

                if (\in_array($acceptableContentType, $contentTypesByFormat, true)) {
                    $httpContentType = $acceptableContentType;
                } elseif ($acceptableContentType === self::WILDCARD) {
                    $httpContentType = $contentTypesByFormat[0];
                }

                if (isset($httpContentType)) {
                    return new Outline(
                        $format,
                        $httpContentType,
                        $serializationContext,
                    );
                }
            }
        }

        throw new ContentTypeMismatchException(sprintf('Cannot match %s mime types with %s formats', implode(', ', $acceptableContentTypes), implode(', ', $knownFormats)));
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

        throw new PresetNotFoundException(sprintf('Response body preset "%s" not found, available: %s', $name, implode(', ', $available)));
    }
}
