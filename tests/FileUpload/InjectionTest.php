<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\FileUpload;

use Acsiomatic\HttpPayloadBundle\FileUpload\Exception\PresetNotFoundException;
use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\DummyKernel;
use Acsiomatic\HttpPayloadBundle\Tests\ResourceTrait;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class InjectionTest extends TestCase
{
    use ResourceTrait;

    public function testInjectsWithDefaults(): void
    {
        $filepath = $this->resourcePath('file-small.txt');

        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('POST', '/file-upload/defaults',
            files: ['file' => new UploadedFile($filepath, 'file-small.txt', 'text/plain')],
            server: ['HTTP_CONTENT_TYPE' => 'multipart/form-data'],
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertStringEqualsFile($filepath, $response->getContent());
    }

    public function testInjectsWithCustomName(): void
    {
        $filepath = $this->resourcePath('file-small.txt');

        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('POST', '/file-upload/custom-name-foo',
            files: ['foo' => new UploadedFile($filepath, 'foobar.txt', 'text/plain')],
            server: ['HTTP_CONTENT_TYPE' => 'multipart/form-data'],
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertIsString($response->getContent());
        self::assertStringEqualsFile($filepath, $response->getContent());
    }

    public function testInjectsNullWhenFileIsMissingAndArgumentIsNullable(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->request('POST', '/file-upload/nullable',
            server: ['HTTP_CONTENT_TYPE' => 'multipart/form-data'],
        );

        $response = $client->getResponse();
        self::assertTrue($response->isSuccessful());
        self::assertEmpty($response->getContent());
    }

    public function testSkipInjectionIfAttributeIsMissing(): void
    {
        $filepath = $this->resourcePath('file-small.txt');

        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessageMatches(sprintf('/Cannot autowire argument \$file of ".*": it references class "%s" but no such service exists./', preg_quote(UploadedFile::class, '/')));

        $client->request('POST', '/file-upload/attribute-missing',
            files: ['file' => new UploadedFile($filepath, 'file.txt', 'text/plain')],
            server: ['HTTP_CONTENT_TYPE' => 'multipart/form-data'],
        );
    }

    public function testThrowsPresetNotFoundExceptionWhenTargetingNonExistingPreset(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(PresetNotFoundException::class);
        $this->expectExceptionMessage('File upload preset "preset_max_size_50_bytes" not found, available: default');

        // @phpstan-ignore-next-line
        self::assertNotInstanceOf(BadRequestHttpException::class, PresetNotFoundException::class);

        $filepath = $this->resourcePath('file-small.txt');
        $client->request('POST', '/file-upload/max-size-50-bytes-preset',
            files: ['file' => new UploadedFile($filepath, 'file-small.txt', 'text/plain')],
            server: ['HTTP_CONTENT_TYPE' => 'multipart/form-data'],
        );
    }
}
