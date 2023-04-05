<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\FileUpload;

use Acsiomatic\HttpPayloadBundle\FileUpload\Exception\InvalidUploadedFileException;
use Acsiomatic\HttpPayloadBundle\FileUpload\Exception\MissingUploadedFileException;
use Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\DummyKernel;
use Acsiomatic\HttpPayloadBundle\Tests\ResourceTrait;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class ValidationTest extends TestCase
{
    use ResourceTrait;

    public function testThrowsMissingUploadedFileExceptionFileWhenFileIsMissingAndArgumentIsNotNullable(): void
    {
        $kernel = new DummyKernel(null);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(MissingUploadedFileException::class);
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('File "file" is missing.');

        $client->request('POST', '/file-upload/not-nullable');
    }

    #[TestWith(['/file-upload/max-size-50-bytes-local', null, 'file-big.txt'])]
    #[TestWith(['/file-upload/max-size-50-bytes-preset', 'file_upload.yaml', 'file-big.txt'])]
    public function testThrowsInvalidUploadedFileExceptionWhenPayloadViolatesConstraints(
        string $uri,
        string|null $configFilename,
        string $filename,
    ): void {
        $kernel = new DummyKernel($configFilename);
        $client = new KernelBrowser($kernel);
        $client->catchExceptions(false);

        $this->expectException(InvalidUploadedFileException::class);
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid uploaded file "file".');

        $filepath = $this->resourcePath($filename);
        $client->request('POST', $uri,
            files: ['file' => new UploadedFile($filepath, 'file-small.txt', 'text/plain')],
            server: ['HTTP_CONTENT_TYPE' => 'multipart/form-data'],
        );

        // @todo check constraint messages: The file is too large (%d bytes). Allowed maximum size is 50 bytes.
    }
}
