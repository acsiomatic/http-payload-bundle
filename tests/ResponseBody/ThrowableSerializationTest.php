<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\ResponseBody;

use Acsiomatic\HttpPayloadBundle\ResponseBody\Exception\ContentTypeMismatchException;
use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\DummyKernel;
use Acsiomatic\HttpPayloadBundle\Tests\ResourceTrait;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\Exception\NotAcceptableHttpException;

final class ThrowableSerializationTest extends TestCase
{
    use ResourceTrait;

    public function testSerializesThrowableAsJsonByDefault(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/response-body/defaults', ['throws' => '1']);
        $response = $client->getResponse();

        self::assertTrue($response->isServerError());
        self::assertIsString($response->getContent());
        self::assertSame('application/json', $response->headers->get('content-type'));
        self::assertJsonStringEqualsJsonFile($this->resourcePath('error.json'), $response->getContent());
    }

    public function testSerializesThrowableAsXml(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/response-body/xml-only', ['throws' => '1']);
        $response = $client->getResponse();

        self::assertTrue($response->isServerError());
        self::assertIsString($response->getContent());
        self::assertSame('application/xml', $response->headers->get('content-type'));
        self::assertXmlStringEqualsXmlFile($this->resourcePath('error.xml'), $response->getContent());
    }

    public function testSerializesThrowableAsYaml(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/response-body/yaml-only', ['throws' => '1']);
        $response = $client->getResponse();

        self::assertTrue($response->isServerError());
        self::assertIsString($response->getContent());
        self::assertSame('application/x-yaml', $response->headers->get('content-type'));
        self::assertStringEqualsFile($this->resourcePath('error.yaml'), $response->getContent());
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
        $client->request('GET', $path, ['throws' => '1'],
            server: ['HTTP_ACCEPT' => $acceptHeader],
        );

        $response = $client->getResponse();

        self::assertTrue($response->isServerError());
        self::assertIsString($response->getContent());
        self::assertSame($expectedContentType, $response->headers->get('content-type'));

        match ($expectedContentType) {
            'application/json' => self::assertJsonStringEqualsJsonFile(
                $this->resourcePath('error.json'),
                $response->getContent()
            ),
            'application/xml' => self::assertXmlStringEqualsXmlFile(
                $this->resourcePath('error.xml'),
                $response->getContent()
            ),
            default => self::throwException(
                new \UnexpectedValueException(sprintf('Unexpected "%s" content type', $expectedContentType))
            )
        };
    }

    public function testContentMismatchExceptionIsNotAcceptableResponse(): void
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

    public function testViolationsAreInThePayloadWhenThrowingConstraintViolationListException(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('GET', '/response-body/throw-contains-constraint-violations-exception',
            server: ['HTTP_ACCEPT' => 'application/json'],
        );

        $response = $client->getResponse();
        self::assertTrue($response->isServerError());
        self::assertSame('application/json', $response->headers->get('content-type'));
        self::assertIsString($response->getContent());
        self::assertJsonStringEqualsJsonFile($this->resourcePath('error-with-constraint-violations.json'), $response->getContent());
    }

    public function testHonorsHttpExceptionStatusCode(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);

        $client->request('GET', '/response-body/throw-http-exception');

        $response = $client->getResponse();
        self::assertSame(418, $response->getStatusCode());
        self::assertSame('application/json', $response->headers->get('content-type'));
        self::assertIsString($response->getContent());
        self::assertJsonStringEqualsJsonFile($this->resourcePath('error-teapot.json'), $response->getContent());
    }

    public function testSkipsSerializationWhenMissingAttribute(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);

        $client->request('GET', '/response-body/missing-attribute', ['throws' => '1']);

        $response = $client->getResponse();
        self::assertTrue($response->isServerError());
        self::assertNotSame('application/json', $response->headers->get('content-type'));
        self::assertIsString($response->getContent());
        self::assertStringNotContainsString('Something wrong is not correct', $response->getContent());
    }
}
