<?php

namespace Acsiomatic\HttpPayloadBundle\RequestBody\ValueResolver;

use Acsiomatic\HttpPayloadBundle\RequestBody\Attribute\AsRequestBody;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\ContentTypeMismatchException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\InvalidRequestBodyException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\MissingArgumentTypeException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\MissingRequestBodyException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\UnexpectedContentTypeException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Exception\UnexpectedRequestBodyException;
use Acsiomatic\HttpPayloadBundle\RequestBody\Outline;
use Acsiomatic\HttpPayloadBundle\RequestBody\OutlineResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Serializer\Exception\UnexpectedValueException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @internal
 */
final readonly class RequestBodyValueResolver implements ValueResolverInterface
{
    public function __construct(
        private OutlineResolver $outlineResolver,
        private SerializerInterface $serializer,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @return iterable<mixed|null>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attribute = $argument->getAttributesOfType(AsRequestBody::class, ArgumentMetadata::IS_INSTANCEOF)[0] ?? null;
        if (!$attribute instanceof AsRequestBody) {
            return [];
        }

        if ($argument->getType() === null) {
            throw new MissingArgumentTypeException(sprintf('Cannot resolve request body for $%s because it has no declared type', $argument->getName()));
        }

        try {
            $outline = $this->outlineResolver->resolve($request, $attribute);
        } catch (ContentTypeMismatchException $exception) {
            throw new UnexpectedContentTypeException($exception->getMessage(), $exception);
        }

        $content = $request->getContent();
        if ($content === '') {
            if ($argument->isNullable()) {
                return [null];
            }

            throw new MissingRequestBodyException('Request body is empty.');
        }

        try {
            $value = $this->serializer->deserialize(
                $content,
                $argument->getType(),
                $outline->format,
                $outline->deserializationContext,
            );
        } catch (UnexpectedValueException $exception) {
            throw new UnexpectedRequestBodyException($exception->getMessage(), $exception->getCode(), $exception);
        }

        try {
            $this->validate($value, $outline);
        } catch (ValidationFailedException $exception) {
            throw new InvalidRequestBodyException('Invalid request body.', $exception);
        }

        return [$value];
    }

    private function validate(mixed $value, Outline $outline): void
    {
        if ($outline->validationGroups === false) {
            return;
        }

        $violations = $this->validator->validate($value, null, $outline->validationGroups);
        if ($violations->count() > 0) {
            throw new ValidationFailedException($value, $violations);
        }
    }
}
