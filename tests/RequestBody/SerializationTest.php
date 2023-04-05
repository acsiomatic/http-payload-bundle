<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\RequestBody;

use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\PresetNotFoundException;
use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\DummyKernel;
use Acsiomatic\HttpPayloadBundle\Tests\ResourceTrait;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class SerializationTest extends TestCase
{
    use ResourceTrait;

    public function testDeserializesAsJsonByDefault(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/request-body/defaults',
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
            content: $this->resourceContent('person.json'),
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame('Json', $response->getContent());
    }

    public function testDeserializesAsXml(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/request-body/xml-only',
            server: ['HTTP_CONTENT_TYPE' => 'application/xml'],
            content: $this->resourceContent('person.xml'),
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame('Xml', $response->getContent());
    }

    public function testDeserializesAsYaml(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/request-body/yaml-only',
            server: ['HTTP_CONTENT_TYPE' => 'application/x-yaml'],
            content: $this->resourceContent('person.yaml'),
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame('Yaml', $response->getContent());
    }

    #[TestWith(['/request-body/json-or-xml-local', 'application/json', 'person.json', 'Json'])]
    #[TestWith(['/request-body/json-or-xml-local', 'application/xml', 'person.xml', 'Xml'])]
    #[TestWith(['/request-body/json-or-xml-preset', 'application/json', 'person.json', 'Json'])]
    #[TestWith(['/request-body/json-or-xml-preset', 'application/xml', 'person.xml', 'Xml'])]
    public function testDeserializesAsMultipleFormats(
        string $path,
        string $contentType,
        string $payloadFile,
        string $personName,
    ): void {
        $kernel = new DummyKernel('request_body.yaml');
        $client = new KernelBrowser($kernel);
        $client->request('GET', $path,
            server: ['HTTP_CONTENT_TYPE' => $contentType],
            content: $this->resourceContent($payloadFile),
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertSame($personName, $response->getContent());
    }

    public function testDeserializesAsNullWhenPayloadIsMissingAndArgumentIsNullable(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/request-body/nullable-json',
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->getContent());
    }

    #[TestWith(['/request-body/local-deserialization-context', null])]
    #[TestWith(['/request-body/preset-deserialization-context', 'request_body.yaml'])]
    public function testDeserializationContextMustBeHonored(
        string $path,
        string|null $configFilename
    ): void {
        $kernel = new DummyKernel($configFilename);
        $client = new KernelBrowser($kernel);
        $client->request('GET', $path,
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
            content: $this->resourceContent('person.json'),
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame('NULL', $response->getContent());
    }

    public function testThrowsPresetNotFoundExceptionWhenTargetingNonExistingPreset(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(PresetNotFoundException::class);
        $this->expectExceptionMessage('Request body preset "json_or_xml" not found, available: default');

        // @phpstan-ignore-next-line
        self::assertNotInstanceOf(BadRequestHttpException::class, PresetNotFoundException::class);

        $client->request('GET', '/request-body/json-or-xml-preset',
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
            content: $this->resourceContent('person.json'),
        );
    }
}
