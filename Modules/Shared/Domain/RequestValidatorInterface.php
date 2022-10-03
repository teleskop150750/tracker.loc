<?php

declare(strict_types=1);

namespace Modules\Shared\Domain;

use Illuminate\Contracts\Validation\Validator;

interface RequestValidatorInterface
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     * @param array<string, mixed> $messages
     * @param array<string, mixed> $customAttributes
     */
    public function validate(array $data, array $rules, array $messages = [], array $customAttributes = []): Validator;
}
