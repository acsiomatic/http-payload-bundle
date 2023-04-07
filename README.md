# HttpPayloadBundle

The `acsiomatic/http-payload-bundle` handles [HTTP payload][http-payload] within [Routes][sf-routing] that behave like  an [API Endpoint][api-endpoint] in [Symfony] applications.

- [Features In Short](#features-in-short)
- [Installing](#installing)
- [Configuration](#configuration)
- [Use Cases](#use-cases)
  - [Receiving Objects](#receiving-objects)
  - [Receiving Files](#receiving-files)
  - [Returning Objects](#returning-objects)
  - [Returning Files](#returning-files)

## Features In Short

[MapRequestBody] is a controller argument [attribute][php-attributes] which:

1. transforms incoming [HTTP Request Body][http-body] into an [Object][php-objects]
2. validates the object using [Symfony Validation][sf-validation]
3. injects the object into the [Route][sf-routing] argument

[MapUploadedFile] is a controller argument [attribute][php-attributes] which:

1. extracts an [UploadedFile][sf-uploaded-file] object from incoming [HTTP Request][http-request]
2. validates the object using [File Constraints][sf-file-constraints]
3. injects the object into the [Route][sf-routing] argument

[ResponseBody] is a route [attribute][php-attributes] which:

1. looks for a suitable response format through [Content Negotiation][http-content-negotiation]
2. [serializes][sf-serializer] the data returned by the dispatched route
3. exceptions thrown after the [kernel.controller][sf-kernel-controller] event are also serialized
4. injects the serialized data into the [Response][sf-response] object

## Installing

```bash
composer require acsiomatic/http-payload-bundle
```

## Configuration

```yaml
# config/packages/acsiomatic_http_payload.yaml

acsiomatic_http_payload:
    request_body:
        default:
            formats: ['json']
            deserialization_context: []
            validation_groups: ['Default']
    file_upload:
        default:
            constraints: []
    response_body:
        default:
            formats: ['json']
            serialization_context: []
```

## Use Cases

### Receiving Objects

The [MapRequestBody] attribute injects the [HTTP Request Body][http-body] into a [Route][sf-routing] argument.
Incoming data is [deserialized][sf-serializer-deserializing] and [validated][sf-validation] before being injected.

```php
# src/Controller/LuckyController.php

namespace App\Controller;

use Acsiomatic\HttpPayloadBundle\RequestBody\Attribute\MapRequestBody;
use App\NumberRange;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class LuckyController
{
    #[Route('/lucky/number', methods: ['GET'])]
    public function number(
        #[MapRequestBody] NumberRange $range,
    ): Response {
        return new Response(
            (string) random_int($range->min, $range->max)
        );
    }
}
```

```php
# src/NumberRange.php

namespace App;

use Symfony\Component\Validator\Constraints as Assert;

final class NumberRange
{
    #[Assert\GreaterThanOrEqual(0)]
    public int $min;

    #[Assert\GreaterThanOrEqual(propertyPath: 'min')]
    public int $max;
}
```

### Receiving Files

The [MapUploadedFile] attribute fetches the file from the [Request][sf-request] and applies [custom constraints][sf-file-constraints] before injecting it into the [Route][sf-routing] argument.

```php
# src/Controller/UserController.php

namespace App\Controller;

use Acsiomatic\HttpPayloadBundle\FileUpload\Attribute\MapUploadedFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

#[AsController]
final class UserController
{
    #[Route('/user/picture', methods: ['PUT'])]
    public function picture(
        #[MapUploadedFile(
            constraints: new File(mimeTypes: ['image/png', 'image/jpeg']),
        )] UploadedFile $picture,
    ): Response {
        return new Response('Your picture was updated');
    }
}
```

### Returning Objects

The [ResponseBody] attribute [serializes][sf-serializer] the object returned by the [Route][sf-routing] and fills the [Response][sf-response] content with it.

```php
# src/Controller/CelebritiesController.php

namespace App\Controller;

use Acsiomatic\HttpPayloadBundle\ResponseBody\Attribute\ResponseBody;
use App\Person;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
final class CelebritiesController
{
    #[Route('/celebrities/einstein', methods: ['GET'])]
    #[ResponseBody]
    public function einstein(): Person
    {
        $person = new Person();
        $person->name = 'Albert Einstein';
        $person->birthdate = new \DateTimeImmutable('1879-03-14');

        return $person;
    }
}
```

```php
# src/Person.php

namespace App;

use Symfony\Component\Serializer\Annotation as Serializer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

final class Person
{
    public string $name;

    #[Serializer\Context([DateTimeNormalizer::FORMAT_KEY => \DateTimeInterface::ATOM])]
    public \DateTimeImmutable $birthdate;
}
```

### Returning Files

See [Streaming File Responses][sf-streaming].

[MapRequestBody]: src/RequestBody/Attribute/MapRequestBody.php
[MapUploadedFile]: src/FileUpload/Attribute/MapUploadedFile.php
[ResponseBody]: src/ResponseBody/Attribute/ResponseBody.php
[Symfony]: https://symfony.com
[api-endpoint]: https://www.cloudflare.com/en-gb/learning/security/api/what-is-api-endpoint
[http-body]: https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#body
[http-content-negotiation]: https://developer.mozilla.org/en-US/docs/Web/HTTP/Content_negotiation
[http-payload]: https://developer.mozilla.org/en-US/docs/Glossary/Payload_body
[http-request]: https://developer.mozilla.org/en-US/docs/Web/HTTP/Messages#http_requests
[php-attributes]: https://www.php.net/manual/en/language.attributes.php
[php-objects]: https://www.php.net/manual/en/language.oop5.php
[sf-file-constraints]: https://symfony.com/doc/current/reference/constraints/File.html
[sf-kernel-controller]: https://symfony.com/doc/current/reference/events.html#kernel-controller
[sf-request]: https://symfony.com/doc/current/components/http_foundation.html#request
[sf-response]: https://symfony.com/doc/current/components/http_foundation.html#response
[sf-routing]: https://symfony.com/doc/current/routing.html
[sf-serializer-deserializing]: https://symfony.com/doc/current/components/serializer.html#deserializing-an-object
[sf-serializer-encoders]: https://symfony.com/doc/current/components/serializer.html#built-in-encoders
[sf-serializer]: https://symfony.com/doc/current/components/serializer.html
[sf-streaming]: https://symfony.com/doc/current/controller.html#streaming-file-responses
[sf-uploaded-file]: https://symfony.com/doc/current/controller/upload_file.html
[sf-validation-constraints]: https://symfony.com/doc/current/validation.html#constraints
[sf-validation-groups]: https://symfony.com/doc/current/validation/groups.html
[sf-validation]: https://symfony.com/doc/current/validation.html
