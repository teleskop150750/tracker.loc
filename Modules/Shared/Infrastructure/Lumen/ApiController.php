<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Lumen;

use Illuminate\Validation\ValidationException;
use Modules\Shared\Application\Command\CommandBusInterface;
use Modules\Shared\Application\Command\CommandInterface;
use Modules\Shared\Application\Command\CommandResponseInterface;
use Modules\Shared\Application\Query\QueryBusInterface;
use Modules\Shared\Application\Query\QueryInterface;
use Modules\Shared\Application\Query\QueryResponseInterface;

class ApiController
{
    public function __construct(
        private readonly QueryBusInterface $queryBus,
        private readonly CommandBusInterface $commandBus,
        private readonly RequestValidator $validator,
    ) {
    }

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $rules
     * @param array<string, mixed> $messages
     * @param array<string, mixed> $customAttributes
     *
     * @return array<string, mixed>
     *
     * @throws ValidationException
     */
    protected function validate(array $data, array $rules, array $messages = [], array $customAttributes = []): array
    {
        $validator = $this->validator->validate($data, $rules, $messages, $customAttributes);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        return $validator->validated();
    }

    protected function ask(QueryInterface $query): QueryResponseInterface
    {
        return $this->queryBus->ask($query);
    }

    protected function dispatch(CommandInterface $command): ?CommandResponseInterface
    {
        return $this->commandBus->dispatch($command);
    }
}
