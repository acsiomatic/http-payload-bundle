<?php

namespace Acsiomatic\HttpPayloadBundle\FileUpload\ValueResolver;

use Acsiomatic\HttpPayloadBundle\FileUpload\Attribute\MapUploadedFile;
use Acsiomatic\HttpPayloadBundle\FileUpload\Exception\InvalidUploadedFileException;
use Acsiomatic\HttpPayloadBundle\FileUpload\Exception\MissingUploadedFileException;
use Acsiomatic\HttpPayloadBundle\FileUpload\Outline;
use Acsiomatic\HttpPayloadBundle\FileUpload\OutlineResolver;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
final readonly class UploadedFileValueResolver implements ValueResolverInterface
{
    public function __construct(
        private OutlineResolver $outlineResolver,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return iterable<UploadedFile|null>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UploadedFile::class) {
            return [];
        }

        $attribute = $argument->getAttributesOfType(MapUploadedFile::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;
        if (!$attribute instanceof MapUploadedFile) {
            return [];
        }

        $name = $attribute->name ?? $argument->getName();
        $file = $request->files->get($name);
        if (!$file instanceof UploadedFile) {
            if ($argument->isNullable()) {
                return [];
            }

            throw new MissingUploadedFileException(sprintf('File "%s" is missing.', $name));
        }

        $outline = $this->outlineResolver->resolve($attribute);

        try {
            $this->validate($file, $outline);
        } catch (ValidationFailedException $exception) {
            throw new InvalidUploadedFileException(sprintf('Invalid uploaded file "%s".', $name), $exception);
        }

        return [$file];
    }

    private function validate(mixed $value, Outline $outline): void
    {
        if ($outline->constraints === []) {
            return;
        }

        $violations = $this->validator->validate($value, $outline->constraints);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($value, $violations);
        }
    }
}
