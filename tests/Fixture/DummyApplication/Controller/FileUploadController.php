<?php

namespace Acsiomatic\HttpPayloadBundle\Tests\Fixture\DummyApplication\Controller;

use Acsiomatic\HttpPayloadBundle\FileUpload\Attribute\AsUploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

#[AsController]
final class FileUploadController
{
    #[Route('/file-upload/defaults', methods: 'POST')]
    #[Route('/file-upload/not-nullable', methods: 'POST')]
    public function defaults(
        #[AsUploadedFile] UploadedFile $file,
    ): Response {
        return new Response($file->getContent());
    }

    #[Route('/file-upload/nullable', methods: 'POST')]
    public function nullable(
        #[AsUploadedFile] UploadedFile|null $file,
    ): Response {
        return new Response($file?->getContent());
    }

    #[Route('/file-upload/attribute-missing', methods: 'POST')]
    public function attributeMissing(
        UploadedFile $file,
    ): Response {
        return new Response($file->getContent());
    }

    #[Route('/file-upload/custom-name-foo', methods: 'POST')]
    public function customNameAnd50bMaxSize(
        #[AsUploadedFile(name: 'foo')] UploadedFile $bar,
    ): Response {
        return new Response($bar->getContent());
    }

    #[Route('/file-upload/max-size-50-bytes-local', methods: 'POST')]
    public function customMaxSize(
        #[AsUploadedFile(constraints: new File(maxSize: '50'))] UploadedFile $file,
    ): Response {
        return new Response($file->getContent());
    }

    #[Route('/file-upload/max-size-50-bytes-preset', methods: 'POST')]
    public function preset(
        #[AsUploadedFile(preset: 'preset_max_size_50_bytes')] UploadedFile $file,
    ): Response {
        return new Response($file->getContent());
    }
}
