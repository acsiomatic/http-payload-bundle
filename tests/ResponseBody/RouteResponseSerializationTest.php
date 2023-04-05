<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\ResponseBody;

use Acsiomatic\HttpPayloadBundle\ResponseBody\Exception\ContentTypeMismatchException;
use Acsiomatic\HttpPayloadBundle\ResponseBody\Exception\PresetNotFoundException;
use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\Dto\Person;
use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\DummyKernel;
use Acsiomatic\HttpPayloadBundle\Tests\ResourceTrait;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\ControllerDoesNotReturnResponseException;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

final class RouteResponseSerializationTest extends TestCase
{
    use ResourceTrait;

    public function testSerializesAsJsonByDefault(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/response-body/defaults');
        $response = $client->getResponse();

        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame('application/json', $response->headers->get('content-type'));
        self::assertJsonStringEqualsJsonFile($this->resourcePath('person-einstein.json'), $response->getContent());
    }

    public function testSerializesAsXml(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/response-body/xml-only');
        $response = $client->getResponse();

        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame('application/xml', $response->headers->get('content-type'));
        self::assertXmlStringEqualsXmlFile($this->resourcePath('person-einstein.xml'), $response->getContent());
    }

    public function testSerializesAsYaml(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/response-body/yaml-only');
        $response = $client->getResponse();

        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame('application/x-yaml', $response->headers->get('content-type'));
        self::assertStringEqualsFile($this->resourcePath('person-einstein.yaml'), $response->getContent());
    }

    #[TestWith(['/response-body/json-or-xml-local', 'application/json', 'application/json', false])]
    #[TestWith(['/response-body/json-or-xml-local', 'application/xml;q=0.8, application/json;q=0.9', 'application/json', false])]
    #[TestWith(['/response-body/json-or-xml-preset', 'application/xml', 'application/xml', true])]
    #[TestWith(['/response-body/json-or-xml-preset', 'application/xml;q=0.9, application/json;q=0.8', 'application/xml', true])]
    public function testContentNegotiation(
        string $path,
        string $acceptHeader,
        string $expectedContentType,
        bool $loadConfigFile,
    ): void {
        $kernel = new DummyKernel($loadConfigFile ? 'response_body.yaml' : null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', $path,
            server: ['HTTP_ACCEPT' => $acceptHeader],
        );

        $response = $client->getResponse();

        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame($expectedContentType, $response->headers->get('content-type'));

        match ($expectedContentType) {
            'application/json' => self::assertJsonStringEqualsJsonFile(
                $this->resourcePath('person-einstein.json'),
                $response->getContent()
            ),
            'application/xml' => self::assertXmlStringEqualsXmlFile(
                $this->resourcePath('person-einstein.xml'),
                $response->getContent()
            ),
            default => self::throwException(
                new \UnexpectedValueException(sprintf('Unexpected "%s" content type', $expectedContentType))
            )
        };
    }

    #[TestWith(['/response-body/serialization-context-local', null])]
    #[TestWith(['/response-body/serialization-context-preset', 'response_body.yaml'])]
    public function testCustomSerializationContextMustBeHonored(
        string $path,
        string|null $configFilename,
    ): void {
        $kernel = new DummyKernel($configFilename);
        $client = new KernelBrowser($kernel);
        $client->request('GET', $path);
        $response = $client->getResponse();

        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame('application/json', $response->headers->get('content-type'));
        self::assertJsonStringEqualsJsonFile($this->resourcePath('person-einstein-missing-height.json'), $response->getContent());
    }

    public function testThrowsContentTypeMismatchExceptionWhenCannotProduceResponseMatchingTheAcceptedContentType(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(ContentTypeMismatchException::class);
        $this->expectException(NotAcceptableHttpException::class);
        $this->expectExceptionMessage('Cannot match application/xml mime types with json formats');

        $client->request('GET', '/response-body/json-only',
            server: ['HTTP_ACCEPT' => 'application/xml'],
        );
    }

    public function testThrowsPresetNotFoundExceptionWhenTargetingNonExistingPreset(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(PresetNotFoundException::class);
        $this->expectExceptionMessage('Response body preset "without_height" not found, available: default');

        $client->request('GET', '/response-body/serialization-context-preset');
    }

    public function testPresetNotFoundExceptionIsNotBadRequestHttpException(): void
    {
        // @phpstan-ignore-next-line We intentionally track the Exception parent class
        self::assertNotInstanceOf(BadRequestHttpException::class, PresetNotFoundException::class);
    }

    public function testSkipsSerializationWhenMissingAttribute(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(ControllerDoesNotReturnResponseException::class);
        $this->expectExceptionMessage(sprintf('The controller must return a "%s" object but it returned an object of type %s.', Response::class, Person::class));

        $client->request('GET', '/response-body/missing-attribute');
    }
}
