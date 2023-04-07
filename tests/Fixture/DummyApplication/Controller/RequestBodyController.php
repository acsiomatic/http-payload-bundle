<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\Controller;

use Acsiomatic\HttpPayloadBundle\RequestBody\Attribute\MapRequestBody;
use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\Dto\Person;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;

#[AsController]
final class RequestBodyController
{
    #[Route('/request-body/defaults', methods: 'GET')]
    #[Route('/request-body/non-nullable-json', methods: 'GET')]
    public function defaults(
        #[MapRequestBody] Person $person,
    ): Response {
        return new Response($person->name);
    }

    #[Route('/request-body/xml-only', methods: 'GET')]
    public function xmlOnly(
        #[MapRequestBody(formats: 'xml')] Person $person,
    ): Response {
        return new Response($person->name);
    }

    #[Route('/request-body/yaml-only', methods: 'GET')]
    public function yamlOnly(
        #[MapRequestBody(formats: 'yaml')] Person $person,
    ): Response {
        return new Response($person->name);
    }

    #[Route('/request-body/json-or-xml-local', methods: 'GET')]
    public function jsonOrXmlOnlyLocal(
        #[MapRequestBody(formats: ['json', 'xml'])] Person $person,
    ): Response {
        return new Response($person->name);
    }

    #[Route('/request-body/json-or-xml-preset', methods: 'GET')]
    public function jsonOrXmlOnlyPreset(
        #[MapRequestBody(preset: 'json_or_xml')] Person $person,
    ): Response {
        return new Response($person->name);
    }

    #[Route('/request-body/nullable-json', methods: 'GET')]
    public function nullable(
        #[MapRequestBody] Person|null $person,
    ): Response {
        return new Response($person?->name);
    }

    #[Route('/request-body/local-deserialization-context', methods: 'GET')]
    public function localDeserializationContext(
        #[MapRequestBody(deserializationContext: [AbstractNormalizer::IGNORED_ATTRIBUTES => ['height']])] Person $person,
    ): Response {
        return new Response(\gettype($person->height));
    }

    #[Route('/request-body/preset-deserialization-context', methods: 'GET')]
    public function presetDeserializationContext(
        #[MapRequestBody(preset: 'without_height')] Person $person,
    ): Response {
        return new Response(\gettype($person->height));
    }

    #[Route('/request-body/local-strict-validation-group-as-array', methods: 'GET')]
    public function localStrictValidationGroupAsArray(
        #[MapRequestBody(validationGroups: ['strict'])] Person $person,
    ): Response {
        return new Response($person->name);
    }

    #[Route('/request-body/local-strict-validation-group-as-string', methods: 'GET')]
    public function localStrictValidationGroupAsString(
        #[MapRequestBody(validationGroups: 'strict')] Person $person,
    ): Response {
        return new Response($person->name);
    }

    #[Route('/request-body/disable-preset-validation', methods: 'GET')]
    public function disablePresetValidation(
        #[MapRequestBody(preset: 'with_strict_validation', validationGroups: false)] Person $person,
    ): Response {
        return new Response($person->name);
    }

    #[Route('/request-body/strict-validation-group-preset', methods: 'GET')]
    public function strictValidationGroupPreset(
        #[MapRequestBody(preset: 'with_strict_validation')] Person $person,
    ): Response {
        return new Response($person->name);
    }

    // @phpstan-ignore-next-line Intentionally no type specified
    #[Route('/request-body/missing-argument-type', methods: 'GET')]
    public function missingArgumentType(
        #[MapRequestBody()] $argumentWithoutType,
    ): Response {
        return new Response((string) $argumentWithoutType);
    }
}
