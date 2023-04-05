<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\RequestBody;

use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\InvalidRequestBodyException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\MissingArgumentTypeException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\MissingRequestBodyException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\UnexpectedContentTypeException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\UnexpectedRequestBodyException;
use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\DummyKernel;
use Acsiomatic\HttpPayloadBundle\Tests\ResourceTrait;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class ValidationTest extends TestCase
{
    use ResourceTrait;

    public function testThrowsMissingRequestBodyExceptionWhenPayloadIsMissingAndArgumentIsNotNullable(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(MissingRequestBodyException::class);
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Request body is empty.');

        $client->request('GET', '/request-body/non-nullable-json',
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
        );
    }

    public function testThrowsInvalidRequestBodyExceptionWhenDeserializedObjectIsInvalid(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(InvalidRequestBodyException::class);
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid request body.');

        $client->request('GET', '/request-body/non-nullable-json',
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
            content: $this->resourceContent('person-invalid-default-short-name-and-negative-height.json'),
        );
    }

    #[TestWith(['/request-body/local-strict-validation-group-as-array'])]
    #[TestWith(['/request-body/local-strict-validation-group-as-string'])]
    #[TestWith(['/request-body/strict-validation-group-preset'])]
    public function testThrowsInvalidRequestBodyExceptionWhenDeserializedObjectIsInvalidWithCustomGroup(
        string $path,
    ): void {
        $kernel = new DummyKernel('request_body.yaml');
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(InvalidRequestBodyException::class);
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid request body.');

        $client->request('GET', $path,
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
            content: $this->resourceContent('person-invalid-strict-low-height.json'),
        );

        // @todo check for violation: This value should be greater than or equal to 20.
    }

    public function testIgnorePresetValidationWhenAttributeDisabledIt(): void
    {
        $kernel = new DummyKernel('request_body.yaml');
        $client = new KernelBrowser($kernel);

        $client->request('GET', '/request-body/disable-preset-validation',
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
            content: $this->resourceContent('person-invalid-strict-low-height.json'),
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertSame('Invalid According to The Script Group', $response->getContent());
    }

    public function testThrowsUnexpectedContentTypeExceptionWhenCannotHandleContentType(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(UnexpectedContentTypeException::class);
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Cannot match "application/json" mime type with "xml" formats');

        $client->request('GET', '/request-body/xml-only',
            server: ['HTTP_CONTENT_TYPE' => 'application/json'],
            content: $this->resourceContent('person.json'),
        );
    }

    #[TestWith(['application/json', "I'm not { a valid json }", 'Syntax error'])]
    #[TestWith(['application/xml', "I'm not < a valid xml />", "Start tag expected, '<' not found"])]
    public function testThrowsUnexpectedRequestBodyExceptionWhenContentIsNotWellFormatted(
        string $contentType,
        string $content,
        string $expectedExceptionMessage,
    ): void {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(UnexpectedRequestBodyException::class);
        $this->expectExceptionMessage($expectedExceptionMessage);

        $client->request('GET', '/request-body/json-or-xml-local',
            server: ['HTTP_CONTENT_TYPE' => $contentType],
            content: $content,
        );
    }

    public function testThrowsMissingArgumentTypeExceptionWhenItIsMissing(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(MissingArgumentTypeException::class);
        $this->expectExceptionMessage('Cannot resolve request body for $argumentWithoutType because it has no declared type');

        $client->request('GET', '/request-body/missing-argument-type');
    }
}
