<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\Controller;

use Acsiomatic\HttpPayloadBundle\ResponseBody\Attribute\ResponseBody;
use Acsiomatic\HttpPayloadBundle\ResponseBody\Contracts\ResponseStackInterface;
use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\Dto\Person;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[AsController]
final class ResponseBodyController
{
    #[Route('/response-body/defaults', methods: 'GET')]
    #[Route('/response-body/json-only', methods: 'GET')]
    #[ResponseBody]
    public function defaults(Request $request): Person
    {
        if ($request->query->get('throws') === '1') {
            throw new \RuntimeException('Something wrong is not correct.');
        }

        return $this->createEinstein();
    }

    #[Route('/response-body/xml-only', methods: 'GET')]
    #[ResponseBody(formats: 'xml')]
    public function xmlOnly(Request $request): Person
    {
        if ($request->query->get('throws') === '1') {
            throw new \RuntimeException('Something wrong is not correct.');
        }

        return $this->createEinstein();
    }

    #[Route('/response-body/yaml-only', methods: 'GET')]
    #[ResponseBody(formats: 'yaml', serializationContext: [YamlEncoder::YAML_INLINE => 2])]
    public function yamlOnly(Request $request): Person
    {
        if ($request->query->get('throws') === '1') {
            throw new \RuntimeException('Something wrong is not correct.');
        }

        return $this->createEinstein();
    }

    #[Route('/response-body/json-or-xml-local', methods: 'GET')]
    #[ResponseBody(formats: ['something_nonexistent', 'json', 'xml'])]
    public function localJsonOrXml(Request $request): Person
    {
        if ($request->query->get('throws') === '1') {
            throw new \RuntimeException('Something wrong is not correct.');
        }

        return $this->createEinstein();
    }

    #[Route('/response-body/json-or-xml-preset', methods: 'GET')]
    #[ResponseBody(preset: 'json_or_xml')]
    public function presetJsonOrXml(Request $request): Person
    {
        if ($request->query->get('throws') === '1') {
            throw new \RuntimeException('Something wrong is not correct.');
        }

        return $this->createEinstein();
    }

    #[Route('/response-body/serialization-context-local', methods: 'GET')]
    #[ResponseBody(serializationContext: [AbstractNormalizer::IGNORED_ATTRIBUTES => ['height']])]
    public function localDeserializationContext(Request $request): Person
    {
        return $this->createEinstein();
    }

    #[Route('/response-body/serialization-context-preset', methods: 'GET')]
    #[ResponseBody(preset: 'without_height')]
    public function presetDeserializationContext(Request $request): Person
    {
        return $this->createEinstein();
    }

    #[Route('/response-body/tweak-response', methods: 'GET')]
    #[ResponseBody]
    public function tweakResponse(
        ResponseStackInterface $responseStack,
    ): null {
        $responseStack->getResponse()->headers->set('x-foo', 'bar');

        return null;
    }

    #[Route('/response-body/tweak-response-and-throw-exception', methods: 'GET')]
    #[ResponseBody]
    public function tweakResponseAndThrowsException(
        ResponseStackInterface $responseStack,
    ): never {
        $responseStack->getResponse()->headers->set('x-baz', 'qux');

        throw new \RuntimeException('Something wrong is not correct.');
    }

    #[Route('/response-body/throw-contains-constraint-violations-exception', methods: 'GET')]
    #[ResponseBody]
    public function throwContainsConstraintViolationsException(): never
    {
        $violations = new ConstraintViolationList([
            new ConstraintViolation('foo is invalid', '', [], null, 'foo', null),
            new ConstraintViolation('bar is also invalid', '', [], null, 'bar', null),
        ]);

        $validationFailedException = new ValidationFailedException(null, $violations);

        throw new \RuntimeException('This contains constraint violations.', 0, $validationFailedException);
    }

    #[Route('/response-body/throw-http-exception', methods: 'GET')]
    #[ResponseBody]
    public function throwHttpException(): never
    {
        throw new HttpException(418, 'Who am I?');
    }

    #[Route('/response-body/missing-attribute', methods: 'GET')]
    public function missingAttribute(Request $request): Person
    {
        if ($request->query->get('throws') === '1') {
            throw new \RuntimeException('Something wrong is not correct.');
        }

        return $this->createEinstein();
    }

    private function createEinstein(): Person
    {
        $einstein = new Person();
        $einstein->name = 'Albert Einstein';
        $einstein->birthdate = new \DateTime('1879-03-14');
        $einstein->height = 171;

        return $einstein;
    }
}
