<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Lumen;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Support\Facades\Validator as ValidatorFacade;
use Laravel\Lumen\Routing\ProvidesConvenienceMethods;
use Modules\Shared\Domain\RequestValidatorInterface;

class RequestValidator implements RequestValidatorInterface
{
    use ProvidesConvenienceMethods;

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     * @param array<string, mixed> $messages
     * @param array<string, mixed> $customAttributes
     */
    public function validate(array $data, array $rules, array $messages = [], array $customAttributes = []): Validator
    {
        return ValidatorFacade::make($data, $rules, $messages, $customAttributes);
    }
}
