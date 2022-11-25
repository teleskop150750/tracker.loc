<?php

declare(strict_types=1);

namespace Modules\Shared\Infrastructure\Bus;

use function Lambdish\Phunctional\map;
use function Lambdish\Phunctional\reindex;

final class CallableFirstParameterExtractor
{
    /**
     * @param callable[] $callables
     *
     * @return callable[][]
     */
    public static function forCallables(iterable $callables): array
    {
        return map(
            self::unflatten(),
            reindex(self::classExtractor(new self()), $callables)
        );
    }

    /**
     * @throws \ReflectionException
     */
    public function extract(mixed $class): ?string
    {
        $reflector = new \ReflectionClass($class);
        $method = $reflector->getMethod('__invoke');

        if ($this->hasOnlyOneParameter($method)) {
            return $this->firstParameterClassFrom($method);
        }

        return null;
    }

    private static function classExtractor(self $parameterExtractor): callable
    {
        return static fn (callable $handler): ?string => $parameterExtractor->extract($handler);
    }

    private static function unflatten(): callable
    {
        return static fn ($value) => [$value];
    }

    private function firstParameterClassFrom(\ReflectionMethod $method): ?string
    {
        return $method->getParameters()[0]?->getType()?->getName();
    }

    private function hasOnlyOneParameter(\ReflectionMethod $method): bool
    {
        return 1 === $method->getNumberOfParameters();
    }
}
